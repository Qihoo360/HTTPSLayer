-- 规则判断
-- 每个业务的 server 配置 access_by_lua 阶段
local _M = {}
local resty_url = require "resty.url"
local easy_captcha_sdk = require("module.captcha.easy_captcha_sdk")
local config = require("module.captcha.config")
local counter = require("module.captcha.counter")
local handler = require("module.captcha.handler")
local qwaf_token = require("module.captcha.qwaf_token")
local tools = require("lib/tools")
local logger = require("lib/logger").new("captcha/rules_check")
local hitLogger = require("lib/logger").new("captcha/rule_hit")
local commonLogger = require("lib/logger").new("captcha/common")

function _M.go(proj)
    local ok, err = pcall(function()
        _M._go(proj)
    end)
    if not ok then
        logger:log(ngx.ERR, "fatal error: " .. err)
    end
end

function _M._go(proj)

    local rule = config.get_proj_path_cfg(proj, ngx.var.uri)
    if not rule then
        return
    end

    local id = ngx.var.remote_addr
    local count = 1

    if rule.id["cookie"] and rule.id["cookie"] ~= "" then
        local ck = ngx.var["cookie_" .. rule.id["cookie"]]
        if ck then
            id = id .. "-" .. ck
        else
            count = count + 2
        end
    end

    -- 判断是否带了验证码校验成功时种的cookie
    local _id = id -- 即使验证通过了，也可能再次触发验证码
    local qwaf_ck = ngx.var["cookie_QWAF"]
    if qwaf_ck then
        -- 判断cookie_QWAF是否为真实的
        local ts = qwaf_token.decrypt(qwaf_ck)
        if not ts then
            -- 假的QWAF，跳转验证码
            commonLogger:log(ngx.ERR, proj, "wrong-qwaf", rule, qwaf_ck)
            ngx.ctx.QWAF_MAN =  string.format("QWAF=%s; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; HttpOnly", qwaf_ck)
            easy_captcha_sdk.redirect2easy_captcha(proj)
        end
        id = id .. "-" .. qwaf_ck
    end

    -- 判断验证码跳转
    local tk = ngx.var.arg_ectk
    if tk then
        -- 来自验证码跳转
        local _, ts = easy_captcha_sdk.decrypt(proj, tk)
        if not ts then
            -- 校验失败，跳验证码
            commonLogger:log(ngx.ERR, proj, "ectk-invalid", rule, tk)
            easy_captcha_sdk.redirect2easy_captcha(proj)
        end
        if ngx.time() - ts < 6 then
            -- 校验成功，种cookie，清理计数，放行
            local tk = qwaf_token.encrypt();
            ngx.ctx.QWAF_MAN = string.format("QWAF=%s; path=/; HttpOnly", tk)
            counter.clear(id, rule.limit)
            id = _id .. "-" .. tk
            commonLogger:log(ngx.INFO, proj, "ectk-valid", rule, id)
        end
    end

    -- 判断请求方法
    if not rule.method[ngx.var.request_method] then
        count = count + 2
    end

    -- 判断GET参数
    for _, get_arg in pairs(rule.get) do
        if not ngx.var["arg_" .. get_arg] then
            count = count + 2
        end
    end

    -- 判断referer
    if not tools.empty(rule.referer) then
        if not ngx.var.http_referer then
            count = count + 2
        else
            local referer = resty_url.parse(ngx.var.http_referer)
            if not referer or not rule.referer[referer.host] then
                count = count + 2
            end
        end
    end

    for _, limit in pairs(rule.limit) do
        local total_count = counter.inc(id, count, limit.seconds)
        if total_count >= limit.count then
            hitLogger:log(ngx.ERR, proj, id, rule.path, rule.handling, limit, total_count)
            if handler[rule.handling] then
                handler[rule.handling](proj, rule, limit, total_count)
            end
            return
        end
    end

end

-- 设置cookie
function _M.header_filter()
    if ngx.ctx.QWAF_MAN then
        if ngx.header["Set-Cookie"] then
            ngx.header["Set-Cookie"] = ngx.header["Set-Cookie"] .. string.format(';%s', ngx.ctx.QWAF_MAN)
        else
            ngx.header["Set-Cookie"] = ngx.ctx.QWAF_MAN
        end
    end
end

return _M
