local str_has_prefix = require("lib.tools").hasPrefix
--
-- 验证码相关配置
--

local _D = {}

local _M = {}

-- 获取共享内存配置
function _M.getShared()
    return ngx.shared.freqctrl_config
end

function _M.get(name)
    return _D[name]
end

function _M.set(name, value)
    _D[name] = value
    return
end

-- 获取项目对应的路径配置
function _M.get_proj_path_cfg(proj, path)
    if not _D["configs"][proj] then
        return nil
    end
    for _, v in pairs(_D["configs"][proj]["path"]) do
        if str_has_prefix(path, v.path) then
            return v
        end
    end
end

-- 获取项目域名对应的salt
function _M.get_proj_salt(proj, domain)
    if not _D["configs"][proj] then
        return nil
    end
    return _D["configs"][proj]["domain"][domain]
end

return _M
