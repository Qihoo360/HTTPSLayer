--
-- certificate_op.lua
-- 需要在init时在共享内存中放入der格式的certificate和private key
-- 如果需要区分项目和机房,则需要通过某种方式获取项目id和机房
--

local _M = {}

local config = require("module/config")
local logger = require("lib/logger").new("certificate")

--
-- 初始化所有的证书到内存中
--
function _M.fetch_all_certs(cert_conf)

    -- 返回结果
    local ret = true
    local cert_detail = {}
    for k, v in pairs(cert_conf['list']) do


        -- 如果两个都匹配，确定证书
        cert_redis_key = v["cert_redis_key"]
        pkey_redis_key = v["pkey_redis_key"]

        if not cert_redis_key then
            logger:log(ngx.ERR, "cert_redis_key is null! " .. k)
            ret = false
            break
        end

        if not pkey_redis_key then
            logger:log(ngx.ERR, "pkey_redis_key is null! " .. k)
            ret = false
            break
        end

        der_cert = _M.get_data_from_redis(cert_redis_key)
        if not der_cert then
            logger:log(ngx.ERR, "get der_cert from redis is empty!" .. cert_redis_key)
            ret = false
            break
        end

        -- 保存证书的内容
        v["der_cert_info"] = der_cert

        pkey_cert = _M.get_data_from_redis(pkey_redis_key)
        if not pkey_cert then
            logger:log(ngx.ERR, "get pkey from redis is empty!" .. pkey_redis_key)
            ret = false
            break
        end

        v["pkey_info"] = pkey_cert
        cert_detail[k] = v
    end

    return cert_detail, ret
end

--
-- 根据域名和项目id检查正在使用的证书
--
function _M.get_cert_by_host(host)
    -- 从共享内存中获取证书信息
    local cert_conf = config.get("cert_detail")
    if cert_conf == nil then
        logger:log(ngx.ERR, "cert detail is nil! " .. host)
        return false, false, false
    end

    -- 查找该host能够使用的证书
    -- 查找方式为字符匹配
    -- 浏览器对于通配符*只匹配域名中的某一级，即通配符*不匹配“.”。如*.so.com匹配www.so.com，但不匹配api.www.so.com
    -- 另外so.com只匹配so.com的证书，*.so.com不能匹配
    -- 所以匹配的规则如下；
    -- 1、获取顶级域名和最低级域名
    -- 2、判断顶级域名是否相等
    -- 3、判断最低级域名是否匹配

    local low_level_index, _ = string.find(host, '%.')
    local low_level_string = string.sub(host, 1, low_level_index-1)
    local top_level_string = string.sub(host, low_level_index+1)
    for k, v in pairs(cert_conf) do
        local certmatch = false

        -- 检查host是否匹配
        for _, regex in pairs(v['regs']) do
            local re_low_level_index, _ = string.find(regex, '%.')
            local re_low_level_string = string.sub(regex, 1, re_low_level_index-1)
            local re_top_level_string = string.sub(regex, re_low_level_index+1)

            -- 首先判断顶级域名是否匹配
            -- 然后判断低级域名标识是否匹配
            if re_top_level_string == top_level_string then
                if re_low_level_string == "*" or re_low_level_string == low_level_string then
                    certmatch = true
                    break
                end
            end
        end

        -- 如果两个都匹配，确定证书
        if certmatch then
            der_cert_info = v["der_cert_info"]
            pkey_info = v["pkey_info"]
        end
    end

    return der_cert_info, pkey_info, certmatch
end

function _M.get_data_from_redis(redis_key)
    local redis_conf = config.redis

    local redis_host = redis_conf["host"]
    local redis_port = redis_conf["port"]
    local redis_password = redis_conf["pwd"]
    local redis_pool_idle_timeout = redis_conf["pool_idle_timeout"]
    local redis_pool_size = redis_conf["pool_size"]

    local redis = require "resty.redis"
    local red = redis:new()
    local ok, err = red:connect(redis_host, redis_port)
    if not ok then
        logger:log(ngx.ERR, "failed to connect: ", err)
        return nil, err
    end

    local ok, err = red:auth(redis_password)
    if not ok then
        logger:log(ngx.ERR, "failed to auth: ", err)
        return nil, err
    end

    local data, err = red:get(redis_key)
    if err then
        logger:log(ngx.ERR, "get certificate error, redis_key is:" .. redis_key, err)
        return nil, err
    end

    local ok, err = red:set_keepalive(redis_pool_idle_timeout, redis_pool_size)
    if err then
        logger:log(ngx.ERR, "set keepalive error:", err)
    end

    return data
end

return _M
