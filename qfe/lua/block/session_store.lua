--
-- ssl_session_store_by_lua_file
-- 把用户的session data按用户id存入redis,redis参数从共享内存获取
--
local ssl_sess = require("ngx.ssl.session")
local sess_op = require("module/session_op")
local logger = require("lib/logger").new("session")
local wrapper = require("prometheus.wrapper")
local common_conf = require("config/common")

-- 如果没有开始cache session则不需要走到这里
if not common_conf.cacheSession then
    logger:log(ngx.INFO, "session cache is closed,skip")
    return
end

-- 分别获取id和session，理论上应该都能够获取到id和session
local sess_id, err = ssl_sess.get_session_id()
if not sess_id then
    logger:log(ngx.ERR, "failed to get session ID: " .. err)
    wrapper:qpsCounterLog(1, "/get_session_id_err", "session", nil, nil, app)
    return
end

local sess, err = ssl_sess.get_serialized_session()
if not sess then
    logger:log(ngx.ERR, "failed to get SSL session from the current connection: " .. err)
    wrapper:qpsCounterLog(1, "/get_session_content_err", "session", nil, nil, app)
    return
end

local function save_it(premature, sess_id, sess)
    local sess = sess_op.set_session_by_id_withred(sess_id, sess)
    if not sess then
        logger:log(ngx.ERR, "failed to save the session by ID " .. sess_id)
        wrapper:qpsCounterLog(1, "/save_session_content_err", "session", nil, nil, app)
        return ngx.exit(ngx.ERROR)
    end
    wrapper:qpsCounterLog(1, "/save_session_sucess", "session", nil, nil, app)
end

-- create a 0-delay timer here...
local ok, err = ngx.timer.at(0, save_it, sess_id, sess)
if not ok then
    logger:log(ngx.ERR, "failed to create a 0-delay timer: " .. err)
    return
end
