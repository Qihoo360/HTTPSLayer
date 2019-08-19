local redis = require "resty.redis"

local ok, new_tab = pcall(require, "table.new")
if not ok or type(new_tab) ~= "function" then
    new_tab = function (narr, nrec) return {} end
end


local _M = new_tab(0, 54)

function _M.new(self, host, port, pass, db, timeout)
    local r, err = redis:new()
    if not r then
        return nil, err
    end

    local ok, err = r:connect(host, port)
    if not ok then
        return nil, err
    end


    local count, err = r:get_reused_times()
    if 0 == count and pass and pass ~= "" then
        ok, err = r:auth(pass)
        if not ok then
            r:close()
            return nil, err
        end
    elseif err then
        r:close()
        return nil, err
    end

    if db then
        local ok, err = r:select(db)
        if not ok then
            r:close()
            return nil, err
        end
    end

    if timeout then
        r:set_timeout(timeout)
    end

    return setmetatable({ _redis = r }, {__index = function (self, cmd)
        if cmd ~= "close" then
            return function (self, ...)
                local method = self._redis[cmd]
                return method(self._redis, ...)
            end
        end

        return function (self)
            local ok, err = self._redis:set_keepalive(40000, 100)
            if not ok then
                self._redis:close()
            end
        end
    end})
end

return _M
