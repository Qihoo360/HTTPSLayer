--[[

    lua_php_utils.lua
    @author karminski <code.karminski@outlook.com>
    @version 140319:1

    @changelog

        140319:1    ADD scandir(), file_exists(), is_numeric(), trim(), round(), count(), empty(),
                        explode(), implode(), print_r(), strrpos(), strpos(), split(), urlencode(),
                        urldecode(), htmlspecialchars(), var_dump() method.
]]--

lua_php_utils = {}

-------------------------------------------------------------------------------
-- A Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- B Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- C Field
-------------------------------------------------------------------------------

--    count()
--    @param    table t
--    @return   integer arr
function lua_php_utils.count(t)
    local count = 0
    if type(t) == "table" then
        for _ in pairs(t) do count = count + 1 end
    end
    return count
end

-------------------------------------------------------------------------------
-- D Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- E Field
-------------------------------------------------------------------------------

--    empty()
--    @param    table t
--    @return   boolean
function lua_php_utils.empty(t)
    local empty = false
    if (t == nil) then
        empty = true
    elseif ((type(t) == "table") and (next(t) == nil)) then
        empty = true
    end
    return empty
end

--    explode()
--    @param    string separator
--    @param    string str
--    @return   table t
function lua_php_utils.explode(separator, str)
    if (separator=='') then return false end
    local pos,t = 0,{}
    -- for each divider found
    for st,sp in function() return string.find(str,separator,pos,true) end do
        table.insert(t,string.sub(str,pos,st-1)) -- Attach chars left of current divider
        pos = sp + 1 -- Jump past current divider
    end
    table.insert(t,string.sub(str,pos)) -- Attach chars right of last divider
    return t
end

-------------------------------------------------------------------------------
-- F Field
-------------------------------------------------------------------------------

--    file_exists()
--    @param    string filename
--    @return   boolean
function lua_php_utils.file_exists(filename)
    local open = io.open
    local close = io.close
    local file_handle, error_message, error_code = open(filename ,"r")
    if f ~= nil then
        close(file_handle)
        return true
    else
        return false
    end
end

-------------------------------------------------------------------------------
-- G Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- H Field
-------------------------------------------------------------------------------

--    htmlspecialchars()
--    @param    string str
--    @return   string
function lua_php_utils.htmlspecialchars(str)
    local subst = {
        ["&"] = "&amp;";
        ['"'] = "&quot;";
        ["'"] = "&apos;";
        ["<"] = "&lt;";
        [">"] = "&gt;";
    }
    if type(str) == "number" then
        return str
    end
    str = tostring(str)
    return (str:gsub("[&\"'<>]", subst))
end

-------------------------------------------------------------------------------
-- I Field
-------------------------------------------------------------------------------

--    implode()
--    @param    string glue
--    @param    table t
--    @return   string
function lua_php_utils.implode(glue, t)
    local glueType = type(glue)
    if(glueType ~= 'string' and glueType ~= 'number') then return false end
    if(type(t) ~= 'table') then return false end
    local r = ''
    local ctr = 0
    for k,v in pairs(t) do
        if ctr ~= 0 then
            r = r .. glue .. v
        else
            r = r .. v
        end
        ctr  = ctr + 1
    end
    return r
end

--    is_numeric()
--    @param    mix var
--    @return   boolean
function lua_php_utils.is_numeric(var)
    if tonumber(var) ~= nil then
        return true
    end
    return false
end

-------------------------------------------------------------------------------
-- J Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- K Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- L Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- M Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- N Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- O Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- P Field
-------------------------------------------------------------------------------

--    print_r()
--    @param    string t
--    @param    string name
--    @param    string indent
--    @return   string str
function lua_php_utils.print_r(t, return_t)
    local tableList = {}
    function table_r (t, name, indent, full)
        local id = not full and name
            or type(name)~="number" and tostring(name) or '['..name..']'
        local tag = indent .. id .. ' = '
        local out = {}  -- result
        if type(t) == "table" then
            if tableList[t] ~= nil then table.insert(out, tag .. '{} -- ' .. tableList[t] .. ' (self reference)')
            else
                tableList[t]= full and (full .. '.' .. id) or id
                if next(t) then -- Table not empty
                    table.insert(out, tag .. '{')
                    for key,value in pairs(t) do
                        table.insert(out,table_r(value,key,indent .. '    ',tableList[t]))
                    end
                    table.insert(out,indent .. '}')
                else table.insert(out,tag .. '{}') end
            end
        else
            local val = type(t)~="number" and type(t)~="boolean" and '"'..tostring(t)..'"' or tostring(t)
            table.insert(out, tag .. val)
        end
        return table.concat(out, '\n')
    end
    local result = table_r(t,name or 'Value',indent or '')
    if (return_t) then
        return result
    end
    print(result)
end

-------------------------------------------------------------------------------
-- Q Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- R Field
-------------------------------------------------------------------------------

--    round()
--    @param    integer num
--    @param    integer idp
--    @return   integer
function lua_php_utils.round(num, idp)
    if(type(num) ~= 'number') then return false end
    return tonumber(string.format("%." .. (idp or 0) .. "f", num))
end

-------------------------------------------------------------------------------
-- S Field
-------------------------------------------------------------------------------

--    scandir()
--    @param    string directory
--    @return   map
function lua_php_utils.scandir(directory)
    local i = 0
    local t = {}
    local popen = io.popen
    local concat = table.concat
    for filename in popen(concat({'ls -a "',directory,'"'})):lines() do
        i = i + 1
        t[i] = filename
    end
    return t
end

--    split()
--    @param    string str
--    @param    string delim
--    @param    integer max_nb
--    @return   string str
function lua_php_utils.split(str, delim, max_nb)
    -- Eliminate bad cases...
    if string.find(str, delim) == nil then
        return { str }
    end
    if max_nb == nil or max_nb < 1 then
        max_nb = 0    -- No limit
    end
    local result = {}
    local pat = "(.-)" .. delim .. "()"
    local nb = 0
    local lastPos
    for part, pos in string.gfind(str, pat) do
        nb = nb + 1
        result[nb] = part
        lastPos = pos
        if nb == max_nb then break end
    end
    -- Handle the last field
    if nb ~= max_nb then
        result[nb + 1] = string.sub(str, lastPos)
    end
    return result
end

--    strpos()
--    @param    string str
--    @param    string f
--    @return   string str
function lua_php_utils.strpos(str, f)
    if str ~= nil and f ~= nil then
        return (string.find(str, f))
    else
        return nil
    end
end

--    strrpos()
--    @param    string str
--    @param    string f
--    @return   string str
function lua_php_utils.strrpos(str, f)
    if str ~= nil and f ~= nil then
        local t = true
        local offset = 1
        local result = nil
        while (t)
        do
            local tmp = string.find(str, f, offset)
            if tmp ~= nil then
                offset = offset + 1
                result = tmp
            else
                t = false
            end
        end
        return result
    else
        return nil
    end
end

-------------------------------------------------------------------------------
-- T Field
-------------------------------------------------------------------------------

--    trim()
--    @param    string str
--    @return   string
function lua_php_utils.trim(str)
    local match = string.match
    return match(str,'^()%s*$') and '' or match(str,'^%s*(.*%S)')
end

-------------------------------------------------------------------------------
-- U Field
-------------------------------------------------------------------------------

--    urldecode()
--    @param    string str
--    @return   string str
function lua_php_utils.urldecode(str)
    if (str) then
        return str:gsub ("%%(%x%x)", function (x) return string.char(tonumber(x, 16)) end)
    end
    return str
end

--    urlencode()
--    @param    string str
--    @return   string str
function lua_php_utils.urlencode(str)
    if (str) then
        str = string.gsub (str, "\n", "\r\n")
        str = string.gsub (str, "([^%w ])", function (c) return string.format ("%%%02X", string.byte(c)) end)
        str = string.gsub (str, " ", "+")
    end
    return str
end

-------------------------------------------------------------------------------
-- V Field
-------------------------------------------------------------------------------

--    var_dump()
--    @param    mix data
--    @param    max_level
--    @param    prefix
--    @return   nil
function lua_php_utils.var_dump(data, max_level, prefix)
    if type(prefix) ~= "string" then
        prefix = ""
    end
    if type(data) ~= "table" then
        print(prefix .. tostring(data))
    else
        print(data)
        if max_level ~= 0 then
            local prefix_next = prefix .. "    "
            print(prefix .. "{")
            for k,v in pairs(data) do
                io.stdout:write(prefix_next .. k .. " = ")
                if type(v) ~= "table" or (type(max_level) == "number" and max_level <= 1) then
                    print(v)
                else
                    if max_level == nil then
                        var_dump(v, nil, prefix_next)
                    else
                        var_dump(v, max_level - 1, prefix_next)
                    end
                end
            end
            print(prefix .. "}")
        end
    end
end

-------------------------------------------------------------------------------
-- W Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- X Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- Y Field
-------------------------------------------------------------------------------

-------------------------------------------------------------------------------
-- Z Field
-------------------------------------------------------------------------------


-- return
return lua_php_utils
