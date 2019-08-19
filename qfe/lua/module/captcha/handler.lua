-- treatment.lua
-- 判断超过阈值后的处理方式
local _M = {}

local captcha = require "module.captcha.easy_captcha_sdk"
local cjson = require "cjson.safe"

-- 调用验证码服务
function _M.verificationCode(proj, rule, limit, total_count)
    captcha.redirect2easy_captcha(proj)
end

-- 打业务自身处理标记
function _M.buildProjectTag(proj, rule, limit, total_count)
    ngx.var.QWAFProjectTag = cjson.encode({
        proj = proj,
        rule = rule,
        limit = limit,
        total_count = total_count
    })
end

return _M
