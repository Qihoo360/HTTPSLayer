-- Copyright (C) by Jiang Yang (jiangyang-pd@360.cn)

local _M = { _VERSION = "1.1.4" }

local find = string.find
local sub = string.sub

_M.CONF = {
    initted = false,
    app = "default",
    idc = "",
    monitor_switch = {
        METRIC_COUNTER_RESPONSES = {},
        METRIC_COUNTER_SENT_BYTES = {},
        METRIC_COUNTER_REVD_BYTES = {},
        METRIC_HISTOGRAM_LATENCY = {},
        METRIC_COUNTER_EXCEPTION = true,
        METRIC_GAUGE_CONNECTS = true,
    },
    log_method = {},
    buckets = {},
    merge_path = false,
    debug = false
}


local function inTable(needle, table_name)
    if type(needle) ~= "string" or type(table_name) ~= "table" then
        return false
    end
    for _, v in ipairs(table_name) do
        if v == needle then
            return true
        end
    end
    return false
end


local function empty(var)
    if type(var) == "table" then
        return next(var) == nil
    end
    return var == nil or var == '' or not var
end


local function explode(separator, str)
    if (separator == '') then return false end
    local pos, t = 0, {}
    -- for each divider found
    for st,sp in function() return find(str, separator, pos, true) end do
        table.insert(t, sub(str, pos, st-1)) -- Attach chars left of current divider
        pos = sp + 1 -- Jump past current divider
    end
    table.insert(t, sub(str, pos)) -- Attach chars right of last divider
    return t
end


function _M:init(user_config)
    for k, v in pairs(user_config) do
        if k == "app" then
            if type(v) ~= "string" then
                return nil, '"app" must be a string'
            end
            self.CONF.app = v
        elseif k == "idc" then
            if type(v) ~= "string" then
                return nil, '"idc" must be a string'
            end
            self.CONF.idc = v
        elseif k == "log_method" then
            if type(v) ~= "table" then
                return nil, '"log_method" must be a table'
            end
            self.CONF.log_method = v
        elseif k == "buckets" then
            if type(v) ~= "table" then
                return nil, '"buckets" must be a table'
            end
            self.CONF.buckets = v
        elseif k == "monitor_switch" then
            if type(v) ~= "table" then
                return nil, '"monitor_switch" must be a table'
            end
            for i, j in pairs(v) do
                if type(self.CONF.monitor_switch[i]) == "table" then
                    self.CONF.monitor_switch[i] = j
                end
            end
        elseif k == "merge_path" then
            if type(v) ~= "string" then
                return nil, '"merge_path" must be a string'
            end
            self.CONF.merge_path = v
        elseif k == "debug" then
            if type(v) ~= "boolean" then
                return nil, '"debug" must be a boolean'
            end
            self.CONF.debug = v
        end
    end

    if self.CONF.debug == false then
        local config = ngx.shared.prometheus_metrics
        config:flush_all()
    end

    local prometheus = require("prometheus.prometheus").init("prometheus_metrics")

    -- QPS
    if not empty(self.CONF.monitor_switch.METRIC_COUNTER_RESPONSES) then
        self:parseLogUri("METRIC_COUNTER_RESPONSES")
        self.metric_requests = prometheus:counter(
            "module_responses",
            "[" .. self.CONF.idc .. "] number of /path",
            {"app", "api", "module", "method", "code"}
        )
    end

    -- 流量 out
    if not empty(self.CONF.monitor_switch.METRIC_COUNTER_SENT_BYTES) then
        self:parseLogUri("METRIC_COUNTER_SENT_BYTES")
        self.metric_traffic_out = prometheus:counter(
            "module_sent_bytes",
            "[" .. self.CONF.idc .. "] traffic out of /path",
            {"app", "api", "module", "method", "code"}
        )
    end

    -- 流量 in
    if not empty(self.CONF.monitor_switch.METRIC_COUNTER_REVD_BYTES) then
        self:parseLogUri("METRIC_COUNTER_REVD_BYTES")
        self.metric_traffic_in = prometheus:counter(
            "module_rcvd_bytes",
            "[" .. self.CONF.idc .. "] traffic in of /path",
            {"app", "api", "module", "method", "code"}
        )
    end

    -- 程序异常
    if not empty(self.CONF.monitor_switch.METRIC_COUNTER_EXCEPTION) then
        self.metric_exceptions = prometheus:counter(
            "module_exceptions",
            "[" .. self.CONF.idc .. "] exceptions",
            {"app", "exception", "module"}
        )
    end

    -- 延迟
    if not empty(self.CONF.monitor_switch.METRIC_HISTOGRAM_LATENCY) then
        self:parseLogUri("METRIC_HISTOGRAM_LATENCY")
        self.metric_latency = prometheus:histogram(
            "response_duration_milliseconds",
            "[" .. self.CONF.idc .. "] http request latency",
            {"app", "api", "module", "method"},
            self.CONF.buckets
        )
    end

    -- 状态
    if not empty(self.CONF.monitor_switch.METRIC_GAUGE_CONNECTS) then
        self.metric_connections = prometheus:gauge(
            "module_connections",
            "[" .. self.CONF.idc .. "] state",
            {"app", "state"}
        )
    end

    if true then
        self.CONF.initted = true
        self.prometheus = prometheus
    end

    return self.CONF.initted
end


function _M:log(app)
    if not self.CONF.initted then
        return nil, "init first.."
    end

    local uri
    local app = app or self.CONF.app
    local method = ngx.var.request_method or ""
    local request_uri = ngx.var.request_uri or ""
    local status = ngx.var.status or ""

    if not request_uri or not method then
        return nil, "empty request_uri|method"
    end
    request_uri = string.lower(request_uri)

    if inTable(method, self.CONF.log_method) then
        uri = self:isLogUri(request_uri, "METRIC_COUNTER_RESPONSES")
        if self.metric_requests and uri then
            self.metric_requests:inc(1, {app, uri, "self", method, status})
        end

        uri = self:isLogUri(request_uri, "METRIC_COUNTER_SENT_BYTES")
        if self.metric_traffic_out and uri then
            self.metric_traffic_out:inc(tonumber(ngx.var.bytes_sent), {app, uri, "self", method, status})
        end

        uri = self:isLogUri(request_uri, "METRIC_COUNTER_REVD_BYTES")
        if self.metric_traffic_in and uri then
            self.metric_traffic_in:inc(tonumber(ngx.var.request_length), {app, uri, "self", method, status})
        end

        uri = self:isLogUri(request_uri, "METRIC_HISTOGRAM_LATENCY")
        if self.metric_latency and uri then
            local tm = (ngx.now() - ngx.req.start_time()) * 1000
            self.metric_latency:observe(tm, {app, uri, "self", method})
        end
    end

    return true
end


function _M:latencyLog(time, api, module_name, method, app)
    if not self.metric_latency or not self.CONF.initted then
        return false
    end
    app = app or self.CONF.app
    method = method or "GET"
    self.metric_latency:observe(time, {app, api, module_name, method})
    return true
end


function _M:counterLog(counter_ins, value, api, module_name, method, code, app)
    if not counter_ins or not self.CONF.initted then
        return false
    end
    method = method or "GET"
    code = code or 200
    module_name = module_name or "self"
    app = app or self.CONF.app
    counter_ins:inc(tonumber(value), {app, api, module_name, method, code})
    return true
end


function _M:qpsCounterLog(times, api, module_name, method, code, app)
    return self:counterLog(self.metric_requests, times, api, module_name, method, code, app)
end


function _M:sendBytesCounterLog(bytes, api, module_name, method, code, app)
    return self:counterLog(self.metric_traffic_out, bytes, api, module_name, method, code, app)
end


function _M:receiveBytesCounterLog(bytes, api, module_name, method, code, app)
    return self:counterLog(self.metric_traffic_in, bytes, api, module_name, method, code, app)
end


function _M:exceptionLog(times, exception, module_name, app)
    if not self.metric_exceptions or not self.CONF.initted then
        return false
    end
    module_name = module_name or "self"
    app = app or self.CONF.app
    self.metric_exceptions:inc(tonumber(times), {app, exception, module_name})
    return true
end


function _M:gaugeLog(value, state, app)
    if not self.metric_connections or not self.CONF.initted then
        return false
    end
    app = app or self.CONF.app
    self.metric_connections:set(value, {app, state})
    return true
end


function _M:getPrometheus()
    if not self.CONF.initted then
        return nil, "init first.."
    end
    return self.prometheus
end


function _M:parseLogUri(monitor_key)
    local res = {}
    local _t = type(self.CONF.monitor_switch[monitor_key])
    if _t == "table" then
        for _, uri in ipairs(self.CONF.monitor_switch[monitor_key]) do
            -- /idxdata/get?type=obx&name=test => /idxdata/get, {"type=obx", name=test}
            local path, params = self:parseUri(uri)
            local uriConf = {
                uri = uri,
                path = path,
                params = params
            }
            table.insert(res, uriConf)
        end
    elseif _t == "boolean" then
        res = self.CONF.monitor_switch[monitor_key]
    else
        res = false
    end
    self.CONF.monitor_switch[monitor_key] = res
end


function _M:isLogUri(request_uri, monitor_key)
    if type(self.CONF.monitor_switch[monitor_key]) ~= "table" then
        return false
    end
    local request_path, request_params = self:parseUri(request_uri)
    for _, uriConf in ipairs(self.CONF.monitor_switch[monitor_key]) do
        if uriConf["path"] == request_path then
            if empty(uriConf["params"]) then
                return uriConf["uri"]
            else
                local ret = true
                for _, param in ipairs(uriConf["params"]) do
                    if not inTable(param, request_params) then
                        ret = false
                        break
                    end
                end
                if ret then
                    return uriConf["uri"]
                end
            end
        end
    end
    return false
end


function _M:parseUri(uri)
    local path = ""
    local params = {}
    local st, _ = find(uri, "?")
    if st == nil then
        path = uri
    else
        path = sub(uri, 1, st-1)
        local param = sub(uri, st+1)
        if param and param ~= "" then
            params = explode("&", param)
        end
    end
    return path, params
end


function _M:metrics()
    local ip = ngx.var.remote_addr or ""
    local st, _ = find(ip, ".", 1, true)
    local sub_ip = ip
    if st == nil then
        sub_ip = ip
    else
        sub_ip = sub(ip, 1, st-1)
    end

    if sub_ip ~= '10' and sub_ip ~= '172' then
        ngx.exit(ngx.HTTP_FORBIDDEN)
    end

    if not self.CONF.initted then
        ngx.say("init first..")
        ngx.exit(ngx.HTTP_OK)
    end

    if self.metric_connections and ngx.var.connections_reading and ngx.var.connections_waiting and ngx.var.connections_writing then
        self.metric_connections:set(ngx.var.connections_reading, {self.CONF.app, "reading"})
        self.metric_connections:set(ngx.var.connections_waiting, {self.CONF.app, "waiting"})
        self.metric_connections:set(ngx.var.connections_writing, {self.CONF.app, "writing"})
    end

    self.prometheus:collect()

    -- 合并下游自定义统计项, merge_path 需跟 metrics 在同一个server下
    if self.CONF.merge_path and type(self.CONF.merge_path) == "string" then
        local res = ngx.location.capture(self.CONF.merge_path)
        if res and res.status == 200 and type(res.body) == "string" and res.body then
            local newstr, _, err = ngx.re.gsub(res.body, "# (HELP|TYPE).*\n", "", "i")
            if newstr then
                ngx.say(newstr)
            else
                ngx.log(ngx.ERR, "error: ", err)
            end
        end
    end
end


return _M
