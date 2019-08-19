--
-- 公共配置类
--
local _M = {}

_M.serverUrlFormat = "http://%s/api/config?%s" -- 定时获取配置接口地址
_M.serverFrequencyUrlFormat = "http://%s/api/frequency-config" -- 定时获取频率控制配置接口地址
_M.idc = "QIHOO_IDC" -- 获取机房环境变量
_M.prometheusApp = "qfe_https" -- prometheusApp,配合prometheus使用
_M.defaultHost = "www.so.com" -- 默认host
_M.cacheSession = false -- 是否开启session缓存,默认不开启
_M.idc_rename = {
    dev = "corp"
}
return _M


