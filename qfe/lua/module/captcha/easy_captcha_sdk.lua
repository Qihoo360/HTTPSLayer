-- 通用验证码服务的sdk
local _M = {}
local ffi = require("ffi")
local config = require("module.captcha.config")
local logger = require("lib/logger").new("captcha/counter")

ffi.cdef[[
    struct in_addr {
        uint32_t s_addr;
    };

    int inet_aton(const char *cp, struct in_addr *inp);
    uint32_t ntohl(uint32_t netlong);

    char *inet_ntoa(struct in_addr in);
    uint32_t htonl(uint32_t hostlong);
]]
local C = ffi.C

function _M.ip2long(ip)
    local inp = ffi.new("struct in_addr[1]")
    if C.inet_aton(ip, inp) ~= 0 then
        return tonumber(C.ntohl(inp[0].s_addr))
    end
    return nil
end

function _M.long2ip(long)
    if type(long) ~= "number" then
        return nil
    end
    local addr = ffi.new("struct in_addr")
    addr.s_addr = C.htonl(long)
    return ffi.string(C.inet_ntoa(addr))
end

function _M.encrypt(proj, ip, url)
    local salt = config.get_proj_salt(proj, ngx.var.host)
    if not salt then
        logger:log(ngx.ERR, string.format("get %s salt fail", proj))
        return nil
    end
    salt = salt.decrypt_salt
    local t = ngx.time()
    local tk = t * 798 - _M.ip2long(ip)
    tk = tk .. t
    tk = string.reverse(tk)
    local hash = ngx.md5(url .. salt .. tk)
    tk = string.sub(hash, 7, 11) .. tk .. string.reverse(string.sub(hash, 14, 18))
    return tk
end

function _M.decrypt(proj, tk)
    local salt = config.get_proj_salt(proj, ngx.var.host)
    if not salt then
        logger:log(ngx.ERR, string.format("get %s salt fail", proj))
        return
    end
    salt = salt.encrypt_salt
    local hash = string.sub(tk, 1, 5) .. string.reverse(string.sub(tk, -5))
    local tk = string.sub(tk, 6, -6)
    local checkHash = ngx.md5(tk .. salt .. tk)
    if string.sub(checkHash, 7, 11) .. string.sub(checkHash, 14, 18) ~= hash then
        return nil, nil
    end
    tk = string.reverse(tk)
    local ts = string.sub(tk, -10)
    local ip = string.sub(tk, 1, -10)
    ip = ts * 249 - ip
    ip = _M.long2ip(ip)

    return ip, ts
end

function _M.redirect2easy_captcha(proj)
    -- dev
    -- online
    local uri = "http://" .. ngx.var.host .. ngx.var.request_uri
    local tk = _M.encrypt(proj, ngx.var.remote_addr, uri);
    if not tk then
        logger:log(ngx.ERR, "encrypt error")
        return
    end
    -- dev
    -- online
     ngx.redirect(string.format("http://qcaptcha.so.com/?ret=%s&tk=%s", ngx.escape_uri(uri), tk))
end

return _M