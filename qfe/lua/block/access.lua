local config = require "module/config"

local _M = {}

function _M.run()
    -- 检查是否已经初始化完毕了，如果没有那么sleep
    local num = 0
    while((config.get("revision") == nil or config.get("cert") == nil or config.get("balancer") == nil) and num <= 3)
    do
        ngx.sleep(5)
        num = num + 1
    end
    return
end

return _M
