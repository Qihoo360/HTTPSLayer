-- counter.lua
-- 频率控制简单计数方法
local _M = {}

local globalConfig = require("module/config")
local redis_conf = globalConfig.redis_counter

local logger = require("lib/logger").new("captcha/counter")
local redis_handler = require "lib.redisHandler"

function _M.inc(key, number, expire)

    local key = key .. "-" .. expire
    local red, err = redis_handler:new(redis_conf.host, redis_conf.port, redis_conf.pwd, redis_conf.db, redis_conf.timeout)

    if not red then
        logger:log(ngx.ERR, "failed get redis: " .. err)
        return
    end

    red:init_pipeline()
    red:incrby(key, number)
    red:ttl(key)
    local result, err = red:commit_pipeline()
    if not result then
        logger:log(ngx.ERR, "failed pipeline redis: " .. err)
        return
    end
    local count = result[1]
    if count == number or result[2] == -1 then
        -- 设置过期时间
        local ok, err = red:expire(key, expire)
        if not ok then
            logger:log(ngx.ERR, "failed to expire: " .. err)
        end
    end
    red:close()
    return count
end

function _M.clear(key, limits)
    local red, err = redis_handler:new(redis_conf.host, redis_conf.port, redis_conf.pwd, redis_conf.db, redis_conf.timeout)

    if not red then
        logger:log(ngx.ERR, "failed get redis: " .. err)
        return
    end

    local keys = {}
    for k, v in pairs(limits) do
        keys[k] = key .. "-" .. v.seconds
    end
    local ok, err = red:del(unpack(keys))
    if not ok then
        logger:log(ngx.ERR, "failed del: " .. err)
        return
    end

    red:close()
end

return _M
