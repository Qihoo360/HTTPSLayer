--
-- 负载均衡算法
--

local _M = {}

local logger = require("lib/logger").new("balancer")

--
-- 随机下发,按权重分配流量
--
function _M.weight_random(vips)
    local total_weight = _M.get_total_weight(vips)
    if total_weight <= 0 then
        logger:log(ngx.ERR, "failed to upstream, total_weight illegal")
        return nil
    end

    --random_weight取值范围:[0,total_weight)
    local random_weight = math.floor(math.random() * total_weight)
    return _M.choose_vip_by_weight(vips, random_weight)
end

-- ip_hash下发,按权重分配流量
function _M.weight_hash(vips)
    local total_weight = _M.get_total_weight(vips)
    if total_weight <= 0 then
        logger:log(ngx.ERR, "failed to upstream, total_weight illegal")
        return nil
    end

    -- 将ip转换为数字，取模作为hash_weight
    local source_ip = tostring(ngx.var.remote_addr)
    local o1, o2, _, _ = source_ip:match("(%d+)%.(%d+)%.(%d+)%.(%d+)")

    -- 通过ip地址前两位计算权重，能将同一网段下的请求尽量发到同一个VIP
    local hash_weight = (2 ^ 8 * o1 + o2) % total_weight

    return _M.choose_vip_by_weight(vips, hash_weight)
end

-- 获取所有vip的权重总和
function _M.get_total_weight(vips)
    local total_weight = 0
    for _, item in pairs(vips) do
        total_weight = total_weight + tonumber(item.w)
    end

    return total_weight
end

-- 根据权重获取vip
function _M.choose_vip_by_weight(vips, weight)
    local upstream_vip = ""
    local vip_weight = tonumber(weight)
    for _, item in pairs(vips) do
        if vip_weight - tonumber(item.w) < 0 then
            upstream_vip = item.vip
            break
        end
        vip_weight = vip_weight - tonumber(item.w)
    end
    return upstream_vip
end

return _M
