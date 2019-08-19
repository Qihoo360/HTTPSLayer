--
-- 日志收集方法
--

local log_conf = require("config/log_conf")
local var_dump = require ("lib/var_dump")
local tools = require('lib/tools')
local cjson_safe = require('cjson.safe')

local _M = {}

local ngx_log_map = {}
ngx_log_map[ngx.DEBUG] = "DEBUG"
ngx_log_map[ngx.INFO] = "INFO"
ngx_log_map[ngx.NOTICE] = "NOTICE"
ngx_log_map[ngx.WARN] = "WARNING"
ngx_log_map[ngx.ERR] = "ERROR"
ngx_log_map[ngx.CRIT] = "CRITICAL"

local ngx_log_var = {
    set = true,
    rewrite = true,
    access = true,
    content = true,
    header_filter = true,
    body_filter = true,
    log = true
}

function _M.log(self, level, ...)
    local args = {...}
    local log  = args
    log['module'] = self._module
    if log_conf.json then
        log['level'] = ngx_log_map[level]
        log['time']  = os.time() * 1000
        log['traceback'] = _M._traceback()
        if ngx_log_var[ngx.get_phase()] then
            log['ngx_var_remote_addr'] = ngx.var.remote_addr
            log['ngx_var_request_uri'] = ngx.var.request_uri
            log['ngx_var_request_method'] = ngx.var.request_method
            log['ngx_req_header'] = cjson_safe.encode(ngx.req.get_headers())
            log['ngx_var_hostname'] = ngx.var.hostname
        end
        local log_json = cjson_safe.encode(log)
        ngx.log(level, "QFE#START#", log_json, "#END")
    else
        local log_text = "\n"
        for k, v in pairs(log) do
            log_text = log_text .. string.format("%s %s\n", k, var_dump(v))
        end

        ngx.log(level, "QFE#START#", log_text, "#END")
    end
end

function _M._traceback()
    local traceback = debug.traceback()
    traceback = tools.explode("\n", traceback)
    local _traceback = {"", "stack traceback:"}
    for k, v in pairs(traceback) do
        if k > 4 then
            _traceback[k-2] = v
        end
    end

    return table.concat(_traceback, "\n")
end

local mt = { __index = _M }

-- log_ngx_var 在init_worker_by_lua阶段无法使用ngx.var
function _M.new(module)
    return setmetatable({_module = module}, mt)
end

return _M
