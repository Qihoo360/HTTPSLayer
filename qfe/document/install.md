# 前台的安装

## 依赖安装
    - OpenResty `Version:1.11.2.3` 以上
    - LuaJIT `Version:2.1.0-beta2`
    - Lua `Version:5.1.4`
    - Redis `Version:2.8.20`

## 支持方式
    - 虚拟机/物理机

## 修改Nginx配置文件


### nginx.conf的修改

nginx.conf的修改可以参考 qfe/conf-default/nginx.conf执行

```text
......

env QIHOO_IDC; # 允许从环境变量读取QIHOO_IDC这个变量

......

http{

    ......

    lua_code_cache on; # 开启lua代码缓存，加快lua代码执行效率
    lua_shared_dict QFECONFIG 5m; # 开辟一块共享内存，用于存储前台从后台的接口中获取到的全局配置项。
    lua_package_path your_lua_lib_paths; # 将你的qfe目录所在地址配置到这里， 值为 "<qfe所在目录>/qfe/lua;"

    # 获取配置信息
    # 初始化阶段的执行入口， 该指令不需要改动
    init_worker_by_lua_block { 
        require("block/init").run();
    }

    # ssl_session是关于ssl握手信息复用的配置，使用session tickets方式， on或者注释掉本行，为打开， off为关闭。建议线上为on或者注释掉本行
    # ssl_session_tickets off;

    # ssl_session是关于ssl握手信息复用的配置，获取session data
    ssl_session_fetch_by_lua_file <absolute_path>/qfe/lua/block/session_fetch.lua; // session_fetch.lua 的绝对路径
    ssl_session_store_by_lua_file <absolute_path>/qfe/lua/block/session_store.lua; // session_store.lua 的绝对路径
    
    ......

}

```



### conf.d的引入

nginx配置文件， 先将qfe/conf-default 重命名为 qfe/conf, 并且置于nginx可以加载的目录地址中。conf.d下为各个业务的配置文件

    
#### 000目录
    
为抛开业务之外的配置，比如说可能需要暴露一个用于做lvs健康检查的地址，暴露一个用于监控的地址。

#### example.com/your.domain.host 目录

为业务的配置文件，比如说有一个业务是综合搜索（后台指定PID为SO_SITE） www.so.com 这个域名， 那么就建立名为 www.so.com的目录， 放置针对这个业务的配置文件。
还有一个业务是音乐搜索（后台指定PID为SO_MUSIC） music.so.com 这个域名， 那么再建立名为yinyue.so.com 这个目录，放置这个业务的配置文件。每个业务的配置文件主要有三个：http.conf, ssl.conf, upstream.conf

先说明一下 这三个配置文件通用配置项及含义

* http.conf

```text

server {
    listen 80; # 监听80端口

	server_name your.domain.host; # 业务的域名， 因业务不同，此处配置不同

    index       index.html index.php index.htm;
    root        <absolute_path>; # 网站的根地址，由于httpslayer全部是代理，此处指向可以被公开访问的目录即可
 
    set $QFE_PID DEMO_PID; # 业务的PID，PID由后台指定，因业务不同，此处配置不同

    # 所有业务均相同，log阶段需要执行的操作入口。
    log_by_lua_block { 
        require("block/log").run();
    }

    # 其他针对这个业务的转发配置

    client_max_body_size 2m; # 一些业务的特殊配置，比如说该业务上传文件的大小有限制，这里就需要针对该场景配置。

    location / {
        proxy_connect_timeout 30s; # connect的超时时间，根据业务实际来配置
        proxy_read_timeout 30s; # read的超时时间，根据业务实际来配置
        proxy_send_timeout 30s; # send的超时时间，根据业务实际来配置

        proxy_pass http://${QFE_PID}_backend; # 使用反向代理
        proxy_ignore_client_abort on; # 代理过程中发生客户端中断，记录499.
        proxy_set_header Host $host; # 设置代理时的HOST
        proxy_set_header QFE-PROXY 1; # 值为1，业务可以通过header头该值获取到该请求来自httpslayer的代理
        proxy_set_header QFE-HTTPS 0; # 值为0， 标志原始的流量通过http访问
        proxy_set_header X-Real-Ip $remote_addr; # 设置源ip
        proxy_set_header X-Forwarded-For $remote_addr; # 设置源ip到xff
    }
}

```

* ssl.conf 只标识与http.conf不同之处

```text

server {
    listen 443 ssl; # 监听443端口

	server_name your.domain.host;

    set $QFE_PID DEMO_PID;

    log_by_lua_block {
        require("block/log").run();
    }

    ssl_certificate      /your_host_to_crt/cert.crt; # 证书.crt文件，占位文件的地址， 要满足nginx的配置文件格式的要求，此处需要配置一个服务器真实存在的证书文件路径
    ssl_certificate_key  /your_host_to_key/cert.key; # 证书.key文件，占位文件的地址， 要满足nginx的配置文件格式的要求，此处需要配置一个服务器真实存在的证书文件路径

    ssl_certificate_by_lua_file <absolute_path>/qfe/lua/block/ssl_certificate.lua; # https卸载程序的执行入口

    # 其他针对这个业务的转发配置

    client_max_body_size 2m;

    location / {
        proxy_connect_timeout 30s;
        proxy_read_timeout 30s;
        proxy_send_timeout 30s;

        proxy_pass http://${QFE_PID}_backend;
        proxy_ignore_client_abort on;
        proxy_set_header Host $host;
        proxy_set_header QFE-PROXY 1;
        proxy_set_header QFE-HTTPS 1; # 值为1 表示原始流量是HTTPS
        proxy_set_header X-Real-Ip $remote_addr;
        proxy_set_header X-Forwarded-For $remote_addr;
    }
}


```

* upstream.conf , 反向代理， 与ssl.conf 和http.conf  的proxy_pass 对应

```text

upstream DEMO_PID_backend { # 格式${QFE_PID}_backend ， 与proxy_pass对应， 业务不同，此处也不一样
    server 127.0.0.1; # 固定为127.0.0.1
    balancer_by_lua_file <absolute_path>/qfe/lua/block/balancer_worker.lua; # 负载均衡程序入口
}

```

以下是不同业务不同的配置， 实际操作中可以复制已有的业务，更改完成如下配置后，在调整上传文件大小，超时时间等业务特殊要求的配置后，即可。

* http.conf, 为http协议的配置

```text

......
server_name <业务的域名>;
......
set $QFE_PID <业务在后台填写的唯一标识>;
......


```

* ssl.conf， 为https协议的配置

```text

......
server_name <业务的域名>;
......
set $QFE_PID <业务在后台填写的唯一标识>;
......

```

* upstream.conf， 为ssl.conf 和http.conf 中用到的 proxy_pass的反向代理服务器的配置

```text
upstream <业务在后台填写的唯一标识>_backend {
......
}
```

## qfe本身的配置文件说明

前台的lua代码运行时也依赖一些lua代码的配置文件，说明如下

1. 将lua/config/log_conf-default.lua 重命名为 将lua/config/log_conf.lua
2. 将lua/config/server_config-default 文件夹 重命名为 lua/config/server_config

待修改的配置文件

1. lua/config/server_config/example.lua
2. lua/config/common.lua

### example.lua

首先要给计划部署的集群取一个名字， 比如说一个位于北京亦庄的电信机房，取名为"bjyzdx"

此时 部署qfe的机器需要有 环境变量 `export QIHOO_IDC=bjyzdx`

并且 lua/config/server_config/example.lua 须重命名为 lua/config/server_config/bjyzdx.lua

这样lua代码中 在读取到 QIHOO_IDC=bjyzdx 的环境变量后 会自动加载 lua/config/server_config/bjyzdx.lua 改配置文件
（相关代码在lua/module/config.lua - loadConfig 方法中）， 当然 你也可以更改此处，不依赖环境变量加载配置文件。

下面具体来说说 `lua/config/server_config/example.lua` 具体的配置都是什么含义

```lua
local _M = {
    server = "", -- 定时接口地址， 还记得 后台的 /api/config接口吗？ 这里就是这个接口的地址， 只需要配置 host:port（或者ip:port） 即可
    salt = "this_is_a_example", -- 接口校验, 该配置项废弃
    updateInterval = 10, -- 定时， qfe是异步轮训的方式去后台拉全局配置信息， 这里配置的是轮训间隔， 单位：秒

    redis = { -- 用于取证书内容的redis配置
        host = "", -- redis host
        port = "", -- redis port
        pwd = "", -- redis auth password
        timeout = 100, -- redis 单个链接的超时时间
        pool_idle_timeout = 600, -- redis连接池空闲时间
        pool_size = 1000,  -- redis 连接池的大小
    }, 
    redis_session = { -- 用于做ssl握手信息复用的redis配置
        host = "",
        port = "",
        pwd = "",
        timeout = 100,
        pool_idle_timeout = 600,
        pool_size = 1000,
    },
    redis_counter = { -- 此redis的配置忽略
        host = "",
        port = "",
        pwd = "",
        timeout = 200,
        db = 1
    },

    debug = false, -- 是否打开debug， 线上环境请关闭
    ispre = 0, -- 默认0即可。
}

return _M

```

### common.lua

```lua
--
-- 公共配置类
--
local _M = {}

_M.serverUrlFormat = "http://%s/api/config?%s" -- 定时获取配置接口地址, 默认即可
_M.serverFrequencyUrlFormat = "http://%s/api/frequency-config" -- 定时获取频率控制配置接口地址， 默认即可，该功能未启用
_M.idc = "QIHOO_IDC" -- 获取机房环境变量， 从哪个环境变量获取机房名称， 默认"QIHOO_IDC"
_M.prometheusApp = "qfe_https" -- prometheusApp,配合prometheus使用
_M.defaultHost = "www.so.com" -- 默认host
_M.cacheSession = false -- 是否开启session缓存,默认不开启
_M.idc_rename = { -- 机房名称的映射，比如说 前台名称为dev的机房 可能在后台配置的名称是 corp。 同名不需要填写。 
    dev = "corp"
}
return _M
```

以上httpslayer，前台的所有配置均已经完成。



## 启动停止重启

使用openresty 或者nginx的日常维护命令即可
比如openresty：

* <absolute_path>/bin/openresty             启动
* <absolute_path>/bin/openresty -s stop      停止
* <absolute_path>/bin/openresty -s reload    重启


    
## 实际使用时，要注意一下几点，对于httpslayer 和 对接的业务需要注意

- 如果使用接入层，业务可以不用开通443端口
- 302强制跳转需要在业务层来开发
- 接入层会通过HTTP协议（80端口）代理流量到后端机器，并通过在Header头中添加特殊字段来标识HTTPS流量供业务使用。
    - `QFE-PROXY	1	标识流量来自接入层`
    - `QFE-HTTPS	0	标识流量为HTTP`
    - `QFE-HTTPS	1	标识流量为HTTPS`
- 用户ip优先使用x-real-ip字段
    - proxy_set_header X-Real-Ip $remote_addr;
    - proxy_set_header X-Forwarded-For $remote_addr;
- 使用默认的qfe/conf/conf.d/example.com配置测试https时，需要配置同目录下的example.crt & example.key
