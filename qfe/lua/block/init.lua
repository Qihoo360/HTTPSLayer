--
-- 初始化程序，配置更新
--
local http = require("resty.http")
local cjson = require("cjson.safe")
local logger = require("lib/logger").new("init")
local config = require("module/config")
local cert_op = require("module/certificate_op")
local common = require("config/common")
local idcClass = require("config/idc")

local _M = {}

-- 共享内存
local shared = config.getShared()

--
-- 格式化共享内存中的数据，解析到config中
--
local function updateLocalConfig(premature)
    -- Nginx结束，不再更新数据
    if premature then
        return
    end

    logger:log(ngx.INFO, "update local config start!")

    -- 记录更新时间
    config.set("lastUpdate", ngx.utctime())

    -- 如果共享内存中的版本号为空，sleep直到能够拿到共享内存的数据
    while (shared:get("REVISION") == nil)
    do
        logger:log(ngx.INFO, 'WAITTING... no REVISION in shared ')
        ngx.sleep(1)
    end

    -- 如果版本一致不更新，直接返回
    if config.get("revision") == shared:get("REVISION") then
        logger:log(ngx.NOTICE, "The revision is the newest! " .. config.get("revision"))
        return
    end

    -- 共享内存中的配置数据为空，直接返回，这种情况需要报警
    local data = shared:get("CONFIG")
    if data == nil then
        logger:log(ngx.ERR, "shared data is null!")
        return
    end

    -- 解析revision
    -- 解析出certs
    -- 解析出balancer
    -- 将配置从共享内存中写入变量中，程序生成的json，不需要捕获异常
    local remoteConfig = cjson.decode(data)
    if type(remoteConfig) ~= "table" then
        logger:log(ngx.ERR, "Malformed remote config")
        return
    else
        -- 设置原始信息到共享内存中
        local balancer_info = remoteConfig["balancer"]
        local cert_info = remoteConfig["cert"]
        local revision = shared:get("REVISION")

        -- 获取证书的详细信息
        local cert_detail, ret = cert_op.fetch_all_certs(cert_info)
        if not ret then
            logger:log(ngx.ERR, "update local config error! the result is " .. revision)
            return
        end

        -- 如果成功了设置版本
        config.set("revision", revision)
        config.set("balancer", balancer_info)
        config.set("cert", cert_info)
        config.set("cert_detail", cert_detail)

        logger:log(ngx.INFO, "update local config successfully! " .. config.get("revision"))
    end

    return
end

--
-- update config data from console api by revision
--
local function updateConfig(premature)
    logger:log(ngx.INFO, "update config start!")

    -- get all config data from remote api
    local hc = http.new()
    hc:set_timeout(config.sockTimeoutMs)

    -- get the config version now
    local REVISION = shared:get("REVISION") or ""
    local idc = idcClass.getIDC()
    local timestamp = ngx.now()
    local token = ngx.md5(idc .. REVISION.. timestamp .. config.salt)

    -- 检查该机器是否是预发机，如果是预发机请求预发地址
    local ispre = config.ispre
    local qs = ngx.encode_args({ idc = idc, v = REVISION, t = timestamp, token = token, ispre = ispre, host="a.b.".. idc.. ".c.d" })

    local url = string.format(common.serverUrlFormat, config.server, qs)
    logger:log(ngx.INFO, "config_url" .. url)
    local res, error = hc:request_uri(url, {
        method = "GET",
        headers = {
            ["Content-Type"] = "application/x-www-form-urlencoded"
        }
    })

    if error then
        logger:log(ngx.ERR, "Failed to connect to server: " .. url .. ";" .. error)
        return
    end

    if res.status ~= 200 then
        logger:log(ngx.ERR, "Failed to update config:" .. url)
        return
    end
    local success, response = pcall(function(str)
        return cjson.decode(str)
    end, res.body)
    if not success then
        logger:log(ngx.ERR, "remote api error:" .. res.body)
        return nil
    end

    if type(response) ~= "table" or response["code"] ~= 0 then
        logger:log(ngx.ERR, "Malformed config response:" .. res.body)
        return
    end

    local code = response['code']
    local revision = response['version']
    local data = response['data']

    if not code == 0 then
        logger:log(ngx.ERR, "Error config response:" .. code)
        return
    else
        -- 项目完整配置保存到共享数据中
        logger:log(ngx.INFO, "config status:ok")
        shared:set('CONFIG', cjson.encode(data))
        shared:set('REVISION', revision)
    end

    -- 如果config中没有版本号，需要立即更新一次本次配置
    if config.get("revision") == nil then
        updateLocalConfig(false)
    end

    return
end

local function initConfig(delay)
    logger:log(ngx.INFO, "ngx.worker.id:" .. ngx.worker.id())

    -- 定时从远端获取配置文件
    if ngx.worker.id() == 0 then
        local intervalGlobal
        intervalGlobal = function(premature)
            if not premature then
                local ok, err = ngx.timer.at(delay, intervalGlobal)
                if not ok then
                    logger:log(ngx.ERR, err)
                end
            end
            updateConfig(premature)
        end

        local ok, err = ngx.timer.at(0, intervalGlobal)
        if not ok then
            logger:log("[timer wrong remote]" .. ngx.ERR, err)
        end
    end

    -- 定时从共享内存中更新配置文件到程序使用的config模块中
    local intervalLocal
    intervalLocal = function(premature)
        if not premature then
            local ok, err = ngx.timer.at(delay, intervalLocal)
            if not ok then
                logger:log("[timer wrong local]" .. ngx.ERR, err)
            end
        end
        updateLocalConfig(premature)
    end

    local ok, err = ngx.timer.at(math.random(3, 9), intervalLocal)
    if not ok then
        logger:log(ngx.ERR, err)
    end
end

function _M.go()
    -- 加载配置
    config.loadConfig()
    local delay = config.updateInterval

    -- 加载配置信息
    initConfig(delay)

    -- 初始化验证码
--    require("module.captcha.init").run(delay)

    -- 初始化普罗米修斯
    require("module.prometheus").init()

    return true
end

function _M.run()
    local ok, err = pcall(_M.go)
    if not ok then
        logger:log(ngx.ERR, err)
    end
end

return _M
