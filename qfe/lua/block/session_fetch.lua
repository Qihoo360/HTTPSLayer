--
-- ssl_session_fetch_by_lua_file
-- 根据用户id从redis中获取用户的session data
--
local ssl_sess = require("ngx.ssl.session")
local sess_op = require("module/session_op")
local logger = require("lib/logger").new("session")
local wrapper = require("prometheus.wrapper")
local common_conf = require("config/common")

local app = wrapper.CONF.app

-- 如果没有开启cache session则直接返回
if not common_conf.cacheSession then
    logger:log(ngx.INFO, "session cache is closed,skip")
    return
end

-- 获取sessionid
local sess_id, err = ssl_sess.get_session_id()
logger:log(ngx.INFO, "get session id success " .. tostring(sess_id))
if not sess_id then
    logger:log(ngx.INFO, "failed to get session ID: " .. err)
    wrapper:qpsCounterLog(1, "/get_session_id_err", "session", nil, nil, app)
    return
end

-- 从redis中获取到session数据
local sess, err = sess_op.get_session_by_id_withred(sess_id)
logger:log(ngx.INFO, "get session success " .. tostring(sess))
if err then
    logger:log(ngx.INFO, "failed to look up the session by ID " .. sess_id .. ": " .. err)
    wrapper:qpsCounterLog(1, "/get_session_from_redis_err", "session", nil, nil, app)
    return
end

if sess == nil then
    logger:log(ngx.INFO, "this session ID get nil session data:" .. sess_id)
    wrapper:qpsCounterLog(1, "/session_is_nil", "session", nil, nil, app)
    return
end

-- 把session数据反序列化
local ok, err = ssl_sess.set_serialized_session(sess)
if not ok then
    logger:log(ngx.ERR, "failed to set SSL session for ID " .. sess_id .. ": " .. err)
    wrapper:qpsCounterLog(1, "/session_is_nil", "session", nil, nil, app)
    return
end
wrapper:qpsCounterLog(1, "/get_session_success", "session", nil, nil, app)
