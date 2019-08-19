--
-- ssl_certificate_by_lua_file
-- 从共享内存中获取证书,证书为der格式,并base64encode
--
local ssl = require("ngx.ssl")
local byte = string.byte
local certop = require("module/certificate_op")
local logger = require("lib/logger").new("cert")
local wrapper = require("prometheus.wrapper")
local tools = require("lib/tools")
local common_conf = require("config/common")

-- 清除证书信息
local ok, err = ssl.clear_certs()
if not ok then
    logger:log(ngx.ERR, "failed to clear existing (fallback) certificates")
    return ngx.exit(ngx.ERROR)
end

-- 优先使用server_name
-- 如果server_name结果为nil，浏览器不支持SNI，提供一个默认的证书
local host, err = ssl.server_name()
if not host then
    host = common_conf.defaultHost
else
    -- 有一些host是ip地址，如果使用ip地址的也使用默认证书
    local host_type = tools.getIpType(host)
    if host_type == 1 or host_type == 2 then
        host = common_conf.defaultHost
    end
end

-- 如果host为空，返回错误
if not host then
    logger:log(ngx.ERR, "failed to fetch host")
    ngx.exit(ngx.ERROR)
end

-- 根据host获取证书
local pem_cert_chain, pem_pkey, err = certop.get_cert_by_host(host)
if type(pem_cert_chain) ~= "string" then
    logger:log(ngx.ERR, "filed to get der cert data")
    wrapper:exceptionLog(1, "get_pcert_error", host)
    return ngx.exit(ngx.ERROR)
end

der_cert_chain, err = ssl.cert_pem_to_der(pem_cert_chain)
if err then
    logger:log(ngx.ERR, "failed to convert certificate chain from PEM to DER!" .. host)
    wrapper:exceptionLog(1, "convert_pcert_error", host)
    return ngx.exit(ngx.ERROR)
end

local ok, err = ssl.set_der_cert(der_cert_chain)
if not ok then
    logger:log(ngx.ERR, "failed to set DER cert: ", err)
    wrapper:exceptionLog(1, "set_pcert_error", host)
    return ngx.exit(ngx.ERROR)
end

der_pkey, err = ssl.priv_key_pem_to_der(pem_pkey)
if err then
    logger:log(ngx.ERR, "failed to convert private key from PEM to DER")
    wrapper:exceptionLog(1, "get_pkey_error", host)
    return ngx.exit(ngx.ERROR)
end

local ok, err = ssl.set_der_priv_key(der_pkey)
if not ok then
    logger:log(ngx.ERR, "failed to set DER private key: ", err)
    wrapper:exceptionLog(1, "set_pkey_error", host)
    return ngx.exit(ngx.ERROR)
end
