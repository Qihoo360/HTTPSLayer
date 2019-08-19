-- sessop.lua
local _M = {}

local config = require("module/config")
local logger = require("lib/logger").new("sessionop")

function _M.get_session_by_id_withred(sess_id)

    local redis_conf = config.redis_session

    local redis_host = redis_conf["host"]
    local redis_port = redis_conf["port"]
    local redis_password = redis_conf["pwd"]
    local redis_pool_idle_timeout = redis_conf["pool_idle_timeout"]
    local redis_pool_size = redis_conf["pool_size"]

    logger:log(ngx.DEBUG, "redis host: " .. redis_host .. ";redis_port: " .. redis_port .. ";redis_password: " .. redis_password)

    local redis = require "resty.redis"
    local red = redis:new()
    local ok, err = red:connect(redis_host, redis_port)
    if not ok then
        logger:log(ngx.ERR, "failed to connect: " .. err)
        return nil
    end

    local ok, err = red:auth(redis_password)
    if not ok then
        logger:log(ngx.ERR, "failed to auth: " .. err)
        return nil
    end

    logger:log(ngx.DEBUG, "get session info by sess_id: " .. sess_id)
    local sess_encode, err = red:get(sess_id)
    if err then
        logger:log(ngx.ERR, "failed to get session data: " .. err)
        return nil
    end

    if sess_encode == ngx.null then
        return nil
    end

    local ok, err = red:set_keepalive(redis_pool_idle_timeout, redis_pool_size)
    if err then
        logger:log(ngx.ERR, "set keepalive error:" .. err)
    end

    logger:log(ngx.DEBUG, "get session data:" .. sess_encode)

    local t = type(sess_encode)
    if t ~= "string" then
        logger:log(ngx.ERR, "get session data is not string")
        return nil
    end

    sess = ngx.decode_base64(sess_encode)
    return sess
end

--
-- 设置session
--
function _M.set_session_by_id_withred(sess_id, sess)
    local redis_conf = config.redis_session

    local redis_host = redis_conf["host"]
    local redis_port = redis_conf["port"]
    local redis_password = redis_conf["pwd"]
    local redis_key_timeout = redis_conf["timeout"]
    local redis_pool_idle_timeout = redis_conf["pool_idle_timeout"]
    local redis_pool_size = redis_conf["pool_size"]

    logger:log(ngx.DEBUG, "redis host: " .. redis_host, ";redis_port: " .. redis_port, ";redis_password: " .. redis_password)

    local redis = require "resty.redis"
    local red = redis:new()
    local ok, err = red:connect(redis_host, redis_port)
    if not ok then
        logger:log(ngx.ERR, "failed to connect: " .. err)
        return
    end
    local ok, err = red:auth(redis_password)
    if not ok then
        logger:log(ngx.ERR, "failed to auth: " .. err)
        return
    end
    sess_encode = ngx.encode_base64(sess)
    ok, err = red:set(sess_id, sess_encode)
    if not ok then
        logger:log(ngx.ERR, "failed to set session data: " .. err)
        return
    end
    red:expire(sess_id, redis_key_timeout)

    local ok, err = red:set_keepalive(redis_pool_idle_timeout, redis_pool_size)
    if err then
        logger:log(ngx.ERR, "set keepalive error:" .. err)
    end

    logger:log(ngx.DEBUG, "set session info by sess_id: " .. sess_id)
    logger:log(ngx.DEBUG, "set session info by sess: " .. sess_encode)
    return ok
end

return _M
