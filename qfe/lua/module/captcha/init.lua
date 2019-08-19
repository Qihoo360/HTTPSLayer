--
-- 验证码初始化模块
--
local globalConfig = require("module/config")
local config = require("module.captcha.config")
local logger = require("lib/logger").new("captcha/init", false)
local http = require("resty.http")
local cjson = require("cjson.safe")
local common = require("config/common")
-- 频率控制配置共享内存
local fc_shared = config.getShared()

local _M = {}

--
-- 格式化频率控制共享内存中的数据，解析到config中
--
local function updateFreqCtrlLocalConfig()

    logger:log(ngx.INFO, "update frequency control local config start!")

    local global_version = fc_shared:get("VERSION")
    if global_version  == nil or global_version == config.get("global_version") then
        return
    end

    logger:log(ngx.INFO, "updateFreqCtrlLocalConfig global version " .. global_version)

    -- 共享内存中的配置数据为空，直接返回，这种情况需要报警
    local configs = fc_shared:get("CONFIG")
    logger:log(ngx.INFO, "updateFreqCtrlLocalConfig " .. configs)
    if configs == nil then
        logger:log(ngx.ERR, "frequency control shared data is null!")
        return
    end
    configs = cjson.decode(configs)

    config.set("global_version", global_version)

    local p_cfgs = config.get("configs")
    if p_cfgs == nil then
        p_cfgs = {}
    end
    for proj, cfg in pairs(configs) do
        local p_cfg = p_cfgs[proj]
        if p_cfg == nil or p_cfg.version ~= cfg.version then
            -- 更新
            p_cfgs[proj] = {version=cfg.version, path=cfg.data.path, domain=cfg.data.domain}
            logger:log(ngx.INFO, string.format("update %s new version %s", proj, cfg.version))
        end
    end
    config.set("configs", p_cfgs)

    logger:log(ngx.INFO, "update local frequency control config successfully! global_version " .. config.get("global_version"))
end

--
-- update config data from frequency control api by revision
-- 频率控制配置接口调用
--
local function updateFreqCtrlConfig()

    logger:log(ngx.INFO, "update freqctrl config start!")

    -- get all config data from remote api
    local hc = http.new()
    hc:set_timeout(2000)

    -- 共享内存
    -- 检查该机器是否是预发机，如果是预发机请求预发地址

    local url = string.format(common.serverFrequencyUrlFormat, globalConfig.server)
    local res, error = hc:request_uri(url, {method = "GET"})

    if error then
        logger:log(ngx.ERR, "Failed to connect to server: " .. url .. ";" .. error)
        return
    end

    if res.status ~= 200 then
        logger:log(ngx.ERR, "Failed to update frequency control config")
        logger:log(ngx.ERR, res.status, res.body)
        return
    end

    local response = cjson.decode(res.body)
    if type(response) ~= "table" or response["code"] ~= 0 then
        logger:log(ngx.ERR, "Malformed frequency control config response:" .. res.body)
        return
    end

    local data = response['data']
    local ver = fc_shared:get("VERSION")
    if ver ~= nil and ver == data["global_version"] then
        -- 没有新的发布，无需更新
        return
    end
    -- 版本变更，有新的发布，更新共享内存
    fc_shared:set("VERSION", data["global_version"])
    fc_shared:set("CONFIG", cjson.encode(data["config"]))
    logger:log(ngx.INFO, "global update version " .. data["global_version"])

    return
end

function _M.run(delay)

    if ngx.worker.id() == 0 then
        -- 频率控制配置定时获取
        local getFreqCtrlRemote
        getFreqCtrlRemote = function(premature)
            if not premature then
                local ok, err = ngx.timer.at(delay, getFreqCtrlRemote)
                if not ok then
                    logger:log(ngx.ERR, "ngx.timer.at error: " .. err)
                end
                updateFreqCtrlConfig()
            end
        end

        local ok, err = ngx.timer.at(0, getFreqCtrlRemote)
        if not ok then
            logger:log(ngx.ERR, "ngx.timer.at error: " .. err)
        end
    end


    -- 定时从共享内存中更新频率控制配置文件到程序使用的config模块中
    local getFreqCtrlLocal
    getFreqCtrlLocal = function(premature)
        if not premature then
            local ok, err = ngx.timer.at(delay, getFreqCtrlLocal)
            if not ok then
                logger:log(ngx.ERR, "ngx.timer.at error: " .. err)
            end
            updateFreqCtrlLocalConfig()
        end
    end

    local ok, err = ngx.timer.at(2, getFreqCtrlLocal)
    if not ok then
        logger:log(ngx.ERR, "ngx.timer.at error: " .. err)
    end
end

return _M
