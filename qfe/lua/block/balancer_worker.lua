local balancer = require "ngx.balancer"
local config = require "module/config"
local blcr_algo = require "module/balancer_algo"
local logger = require "lib/logger".new("balancer")
local wrapper = require("prometheus.wrapper")

-- 后台需要跟进开发
-- 获取对应QFEPID的配置信息
-- 优先使用指定的vip组
local qfe_pid = ngx.var.QFE_PID
if ngx.var.QFE_PID_VIP ~= nil and ngx.var.QFE_PID_VIP ~= "" then
    qfe_pid = ngx.var.QFE_PID_VIP
end

local all_balancer_info = config.get("balancer")
local balancer_info = all_balancer_info[qfe_pid]

-- 获取 Upstream VIP
local upstream_vip = blcr_algo.weight_hash(balancer_info.vips)
if upstream_vip == nil then
    logger:log(ngx.ERR, "failed to upstream, illegal vip, qfe pid: " .. qfe_pid)
    wrapper:exceptionLog(1, "get_vip_error", qfe_pid)
    return ngx.exit(500)
end

-- 默认的端口
local default_port = 80

-- 代理到目的ip
local upstream_host = upstream_vip
local upstream_port = default_port

-- 判断下upstream vip的格式，如果包含端口了，要重新解析
local vip_division_index = string.find(upstream_vip, ":", 1, true)
if vip_division_index ~= nil then
    upstream_host = string.sub(upstream_vip, 1, vip_division_index - 1)
    upstream_port  = string.sub(upstream_vip, vip_division_index + 1, #upstream_vip)
end

if upstream_host == nil then
    logger:log(ngx.ERR, "failed to get upstream_host: " .. upstream_vip)
    return ngx.exit(500)
end

-- set max retry times
balancer.set_more_tries(3)
local ok, err = balancer.set_current_peer(upstream_host, upstream_port)
if not ok then
    logger:log(ngx.ERR, "failed to set the current peer: " .. err)
    wrapper:exceptionLog(1, "set_current_peer_error", qfe_pid)
    return ngx.exit(500)
end

local method = ngx.var.request_method or ""
local app = wrapper.CONF.app
wrapper:qpsCounterLog(1, "/" .. upstream_host, qfe_pid, method, nil, app)
logger:log(ngx.INFO, "set current peer success " .. upstream_host, ":" .. upstream_port)
