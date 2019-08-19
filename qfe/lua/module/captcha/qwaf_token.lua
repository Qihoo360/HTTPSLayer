local _M = {}

function _M.encrypt()
    local salt = "demo-salt"
    local t = string.format("%x", ngx.time())
    local hash = ngx.md5(salt .. t)
    local tk = string.sub(hash, 7, 11) .. t .. string.reverse(string.sub(hash, 14, 18))
    return tk
end

function _M.decrypt(tk)
    local salt = "demo-salt"
    local t = string.sub(tk, 6, -6)
    local hash = ngx.md5(salt .. t)
    local validate_tk = string.sub(hash, 7, 11) .. t .. string.reverse(string.sub(hash, 14, 18))
    if tk == validate_tk then
        return tonumber("0x"..t)
    end
    return nil
end

return _M
