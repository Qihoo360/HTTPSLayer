--
-- 配置类
--

local idcClass = require("config/idc")

-- 真正程序使用的数据配置，需要从共享内存中解析出来
local _D = {
    revision = nil,
    lastUpdateTime = nil,
    certs = nil,
    balancer = nil
}

-- 常量配置
local _M = {}

--
-- 加载系统的配置
--
function _M.loadConfig()
    local idc = idcClass.getIDC()

    local status, config = pcall(require, "config/server_config/" .. idc)
    if status then
        _M.idc = idc
        for k, v in pairs(config) do
            _M[k] = v
        end
    else
        error("not supported idc " .. idc)
    end
end

--
-- 获取当前版本
--
function _M.getRevision()
    return _D['revision']
end

--
-- 获取共享内存
--
function _M.getShared()
    return ngx.shared.QFECONFIG
end

function _M.get(name)
    return _D[name]
end

function _M.set(name, value)
    _D[name] = value
    return
end

return _M
