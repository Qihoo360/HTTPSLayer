local common = require("config/common")
local idcClass = require("config/idc")

if not cjson then
    cjson = require "cjson.safe"
end
local function empty(var)
    if type(var) == 'table' then
        return next(var) == nil
    end
    return var == nil or var == '' or not var
end

local function emptyStr(var)
    return var == nil or var == '' or tostring(var) == 'nil' or not var
end

local function emptyNum(var)
    return var == nil or var == '' or tonumber(var) <= 0 or tonumber(var) == nil or not var
end

local function trim(str)
    if type(str) ~= 'string' then
        return ''
    end
    local s = string.gsub(str, "^%s*(.-)%s*$", "%1")
    return s
end

local function mergeTable(t1, t2)
    local new_table = {}
    for i, v in ipairs(t1) do
        table.insert(new_table, v)
    end
    for i, v in ipairs(t2) do
        table.insert(new_table, v)
    end
end

local function htmlspecialchars(str)
    local str, n, err = ngx.re.gsub(str, "&", "&amp;")
    str, n, err = ngx.re.gsub(str, "<", "&lt;")
    str, n, err = ngx.re.gsub(str, ">", "&gt;")
    str, n, err = ngx.re.gsub(str, '"', "&quot;")
    str, n, err = ngx.re.gsub(str, "'", "&apos;")
    return str
end

local function Split(szFullString, szSeparator)

    local nFindStartIndex = 1
    local nSplitIndex = 1
    local nSplitArray = {}
    while true do
        local nFindLastIndex = string.find(szFullString, szSeparator, nFindStartIndex)
        if not nFindLastIndex then
            nSplitArray[nSplitIndex] = string.sub(szFullString, nFindStartIndex, string.len(szFullString))
            break
        end
        nSplitArray[nSplitIndex] = string.sub(szFullString, nFindStartIndex, nFindLastIndex - 1)
        nFindStartIndex = nFindLastIndex + string.len(szSeparator)
        nSplitIndex = nSplitIndex + 1
    end
    return nSplitArray
end

local function readJsonFile(path)
    local fp = io.open(path, "rb")
    local template = fp:read('*a')
    fp:close()
    return cjson.decode(template)
end

local function writefile(path, data)
    local fp = io.open(path, "w")
    local template = fp:write(data)
    fp:close()
    return template
end

local function http_build_query_sort(data, prefix, sep, _key)
    local ret = {}
    local prefix = prefix or ''
    local sep = sep or '&'
    local _key = _key or ''

    local keyt = {}
    for k, _ in pairs(data) do
        table.insert(keyt, k)
    end
    table.sort(keyt)
    local sort_table = {}
    for _, key in ipairs(keyt) do
        local k = key
        local v = data[k]
        if (type(k) == "number" and prefix ~= '') then
            k = prefix .. k
        end
        if (_key ~= '' or _key == 0) then
            k = ("%s[%s]"):format(_key, k)
        end
        if (type(v) == 'table') then
            table.insert(ret, _http_build_query(v, '', sep, k))
        else
            table.insert(ret, ("%s=%s"):format(k, tostring(v)))
        end
    end
    return table.concat(ret, sep)
end

local function clearHTML(html)
    html = string.gsub(html, '<script[%a%A]->[%a%A]-</script>', '')
    html = string.gsub(html, '<style[%a%A]->[%a%A]-</style>', '')
    html = string.gsub(html, '<[%a%A]->', '')
    --删除空行
    html = string.gsub(html, '\n\r', '\n')
    html = string.gsub(html, '%s+\n', '\n')
    html = string.gsub(html, '\n+', '\n')
    html = string.gsub(html, '\n%s+', '\n')
    --删除前后空格
    html = string.gsub(html, '^%s+', '')
    html = string.gsub(html, '%s+$', '')

    return html
end

--ip to int
local function ip2long(s)
    if s == nil then
        return nil
    end
    local r = 0
    local i = 3
    for d in s:gmatch("%d+") do
        r = r + d * 256 ^ i
        i = i - 1
        if i < 0 then
            break
        end
    end
    return r
end

-- int to ip
local function long2ip(i)
    if i == nil then
        return nil
    end
    local r = ""
    for j = 0, 3, 1 do
        r = i % 256 .. "." .. r
        i = math.floor(i / 256)
    end
    return r:sub(1, -2)
end

--exlode
local function explode(delimiter, str, ...)
    if delimiter == '' then
        return str
    end
    local pos, arr = 0, {}
    local limit = ...
    local num = 0
    for st, sp in function()
        return string.find(str, delimiter, pos, true)
    end do
        table.insert(arr, string.sub(str, pos, sp - 1))
        pos = sp + 1
        num = num + 1
        if limit and num == limit then
            break
        end
    end
    table.insert(arr, string.sub(str, pos))
    return arr
end

-- 是否在数组中
local function inArray(value, tbl)
    for k, v in ipairs(tbl) do
        if v == value then
            return true
        end
    end
    return false
end

local function prometheusApp()
    return common.prometheusApp
end

-- 获取hostname
local function getHostname()
    local t = io.popen("hostname")
    return trim(t:read("*all")) or ""
end


local function hasPrefix(str, prefix)
    if type(prefix) ~= "string" then
        return false
    end
    for i = 1, string.len(prefix) do
        if string.byte(str, i) ~= string.byte(prefix, i) then
            return false
        end
    end
    return true
end

local function isK8s()
    return false
end

--
-- 获取字符串的类型
-- 0 错误地址
-- 1 ip4
-- 2 ip6
-- 3 string
--
local function getIpType(ip)
    -- must pass in a string value
    if ip == nil or type(ip) ~= "string" then
        return 0
    end

    -- check for format 1.11.111.111 for ipv4
    local chunks = { ip:match("(%d+)%.(%d+)%.(%d+)%.(%d+)") }
    if (#chunks == 4) then
        for _, v in pairs(chunks) do
            if (tonumber(v) < 0 or tonumber(v) > 255) then
                return 0
            end
        end
        return 1
    else
        return 0
    end

    -- check for ipv6 format, should be 8 'chunks' of numbers/letters
    local _, chunks = ip:gsub("[%a%d]+%:?", "")
    if chunks == 8 then
        return 2
    end

    -- if we get here, assume we've been given a random string
    return 3
end

local function getIDC()
    return idcClass.getIDC()
end

return {
    ip2long = ip2long,
    long2ip = long2ip,
    explode = explode,
    inArray = inArray,
    empty = empty,
    emptyNum = emptyNum,
    emptyStr = emptyStr,
    trim = trim,
    Split = Split,
    htmlspecialchars = htmlspecialchars,
    readJsonFile = readJsonFile,
    writefile = writefile,
    http_build_query_sort = http_build_query_sort,
    clearHTML = clearHTML,
    hasPrefix = hasPrefix,
    getIpType = getIpType,
    isK8s = isK8s,
    prometheusApp = prometheusApp,
    getHostname = getHostname,
    getIDC = getIDC,
}
