--
-- 机房配置类
--
local common = require("config/common")

local _M = {}

function _M.getIDC()
    local idc_env = os.getenv(common.idc) or ""
    if common.idc_rename ~= nil and common.idc_rename[idc_env] ~= nil then
        idc_env = common.idc_rename[idc_env]
    end
    if idc_env == '' then
        error("error occurred, could not find idc anywhere")
    end

    return idc_env
end

return _M
