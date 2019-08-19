local resty_http = require("resty.http")
local cjson = require("cjson.safe")

local SENTRY_VERSION = "5"
local RAVEN_VERSION = "0.1.0"
local DEBUG = false

local ngx = ngx
local setmetatable = setmetatable
local tostring = tostring

local math_random = math.random
local math_fmod = math.fmod
local math_floor = math.floor
local string_format = string.format
local table_insert = table.insert
local table_concat = table.concat
local debug_getinfo = debug.getinfo

local _M = {}

math.randomseed(os.time())
-- Quick implementation of "random" UUID
local function uuid()
    local template = 'xxxxxxxxxxxx4xxxyxxxxxxxxxxxxxxx'
    return ngx.re.gsub(template, '[xy]', function(c)
        local v = (c == 'x') and math_random(0, 0xf) or math_random(8, 0xb)
        return string_format('%x', v)
    end, 'jo')
end

local function table_merge(t, src)
    for k, v in pairs(src) do
        t[k] = v
    end
end

-- Convert integral value version number to string (1004003 -> 1.4.3)
local function int2version(ver)
    local vers = { 0, 0, 0 }
    for i = 1, 3 do
        vers[i] = math_fmod(ver, 1000)
        ver = math_floor(ver / 1000)
    end
    return string_format("%d.%d.%d", vers[3], vers[2], vers[1])
end

local function backtrace(level)
    local frames = {}

    level = level + 1

    while true do
        local info = debug_getinfo(level, "Snl")
        if not info then
            break
        end

        table_insert(frames, 1, {
            filename = info.short_src,
            ["function"] = info.name,
            lineno = info.currentline,
        })

        level = level + 1
    end
    return { frames = frames }
end

local function culprit(level)
    local culprit

    level = level + 1
    local info = debug_getinfo(level, "Snl")
    if info.name then
        culprit = info.name
    else
        culprit = info.short_src .. ":" .. info.linedefined
    end
    return culprit
end

function _M.new(dsn, tags)
    local mt = { __index = _M }

    if not tags then tags = {} end

    local settings, err = _M.parseDSN(dsn)
    if err then error(err) end

    local t = {
        dsn = dsn,
        tags = tags,
        id = settings.id,
        public_key = settings.public,
        secret_key = settings.secret,
        uri = settings.uri .. "/api/" .. tostring(settings.id) .. "/store/",
        hc = resty_http.new(),

        -- API attributes
        sdk = {
            name = "raven-resty",
            version = RAVEN_VERSION,
        },
        device = {
            name = "OpenResty",
            version = int2version(ngx.config.ngx_lua_version),
            build = "Nginx/" .. int2version(ngx.config.nginx_version),
        },
    }
    t.hc:set_timeout(200)
    return setmetatable(t, mt)
end

-- {PROTOCOL}://{PUBLIC_KEY}:{SECRET_KEY}@{HOST}/{PROJECT_ID}
function _M.parseDSN(dsn)
    local m, err = ngx.re.match(dsn, [[^(http[s]*)://([0-9A-Za-z]+):([0-9A-Za-z]+)@([^:/]+)(?::(\d+))?/(\d+)]], "jo")
    if not m then
        if err then
            return nil, "failed to parse dsn: " .. err
        end
        return nil, "invalid dsn"
    end

    local settings = {
        uri = m[1] .. "://" .. m[4],
        public = m[2],
        secret = m[3],
        id = m[6],
    }
    if m[5] then
        settings.uri = settings.uri .. ":" .. m[5]
    end
    return settings, nil
end

function _M:_buildAuth()
    local params = {
        "sentry_version=" .. tostring(SENTRY_VERSION),
        "sentry_client=" .. tostring(RAVEN_VERSION),
        "sentry_timestamp=" .. tostring(ngx.time()),
        "sentry_key=" .. self.public_key,
        "sentry_secret=" .. self.secret_key,
    }
    return "Sentry " .. table_concat(params, ",")
end

function _M:_buildAttributes(message, level)
    -- Avoid "API disabled in the context" error
    local server_name = pcall(function() return ngx.var.server_name end)
    local attrs = {
        event_id = uuid(),
        message = message,
        timestamp = os.date('!%Y-%m-%dT%H:%M:%S'),
        level = level,
        logger = "root",
        platform = "other",
        server_name = server_name or "undefined",
        sdk = self.sdk,
        device = self.device,
        tags = {},
    }
    table_merge(attrs.tags, self.tags)

    return attrs
end

function _M:_buildRequestInterface()
    -- Avoid "API disabled in the context" error
    local interface = pcall(function()
        return {
            url = ngx.var.uri,
            method = ngx.req.get_method(),
            headers = ngx.req.get_headers(),
            query_string = ngx.var.query_string,
            env = {
                REMOTE_ADDR = ngx.var.remote_addr,
            }
        }
    end)
    return inerface or {}
end

function _M:_send(playload)
    local res, error = self.hc:request_uri(self.uri, {
        method = "POST",
        headers = {
            ["User-Agent"] = "Raven-Resty/" .. RAVEN_VERSION,
            ["Content-Type"] = "application/json",
            ["X-Sentry-Auth"] = self:_buildAuth(),
        },
        body = cjson.encode(playload)
    })

    if error then
        if DEBUG then ngx.log(ngx.ERR, error) end
        return nil
    end
    if res.status == 200 then
        local data = cjson.decode(res.body)
        if data then
            return data.id
        else
            return nil
        end
    else
        if DEBUG and res.status >= 300 then
            local error = ngx.req.get_headers()['X-Sentry-Error']
            if error then ngx.log(ngx.ERR, error) end
            return nil
        end
    end
end

function _M:captureMessage(message, options)
    if not options then options = {} end
    local attrs = self:_buildAttributes(message, options.level or "info")
    attrs.request = self:_buildRequestInterface()
    attrs.culprit = culprit(2)

    if options.tags then
        table_merge(attrs.tags, options.tags)
    end
    if options.fingerprint then attrs.fingerprint = options.fingerprint end
    if options.extra then attrs.extra = options.extra end

    return pcall(_M._send, self, attrs)
end

function _M:captureException(exception, options)
    if not options then options = {} end
    local message = tostring(exception)
    local attrs = self:_buildAttributes(message, options.level or "error")
    attrs.request = self:_buildRequestInterface()
    attrs.exception = {
        values = {
            {
                type = "Error",
                value = message,
                stacktrace = backtrace(2),
            }
        }
    }
    attrs.culprit = culprit(2)

    if options.tags then
        table_merge(attrs.tags, options.tags)
    end
    if options.fingerprint then attrs.fingerprint = options.fingerprint end
    if options.extra then attrs.extra = options.extra end

    return pcall(_M._send, self, attrs)
end

return _M
