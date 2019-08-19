local _M = {}

local logger = require("lib/logger").new("prometheus")
local tools = require('lib/tools')

function _M.init()
    -- prometheus 接入
    local ok, err = require("prometheus.wrapper"):init({
        app = tools.prometheusApp(),
        idc = tools.getIDC(),
        monitor_switch = {
            METRIC_COUNTER_RESPONSES = true,
            METRIC_COUNTER_SENT_BYTES = true,
            METRIC_COUNTER_REVD_BYTES = true,
            METRIC_HISTOGRAM_LATENCY = true,
            METRIC_COUNTER_EXCEPTION = true,
            METRIC_GAUGE_CONNECTS = true,
        },

        -- 桶距配置
        buckets = {10,11,13,15,17,19,22,25,28,32,36,41,47,54,62,71,81,92,105,120,137,156,178,203,231,263,299,340,387,440,500}
    })

    if not ok then
        logger:log(ngx.ERR, "prometheus init error: ", err)
    end
end

return _M
