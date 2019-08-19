local _M = {
    server = "", -- 定时接口地址
    salt = "this_is_a_example", -- 接口校验
    updateInterval = 10, -- 定时

    redis = {
        host = "",
        port = "",
        pwd = "",
        timeout = 100,
        pool_idle_timeout = 600,
        pool_size = 1000,
    },
    redis_session = {
        host = "",
        port = "",
        pwd = "",
        timeout = 100,
        pool_idle_timeout = 600,
        pool_size = 1000,
    },
    redis_counter = {
        host = "",
        port = "",
        pwd = "",
        timeout = 200,
        db = 1
    },

    debug = false,
    ispre = 0,
}

return _M
