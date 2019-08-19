local wrapper = require("prometheus.wrapper")

local _M = {}

function _M.run()
    local qfe_pid = ngx.var.QFE_PID
    local method = ngx.var.request_method or ""
    local status = ngx.var.status or ""
    local app = wrapper.CONF.app
    local request_path = "/"
    if ngx.var.request_uri then
        local parsed_path, _ = wrapper:parseUri(ngx.var.request_uri)
        if not parsed_path == nil then
            request_path = parsed_path
        end
    end

    wrapper:qpsCounterLog(1, request_path, qfe_pid, method, status, app)
    wrapper:receiveBytesCounterLog(tonumber(ngx.var.bytes_sent), "/", qfe_pid, method, status, app)
    wrapper:sendBytesCounterLog(tonumber(ngx.var.request_length), "/", qfe_pid, method, status, app)
end

return _M
