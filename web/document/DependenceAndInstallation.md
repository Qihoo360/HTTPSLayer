# web管理平台的安装步骤

## 依赖

* PHP >= 7.0 开启fpm, 须添加php-redis扩展 且支持composer
    * php官方文档：https://www.php.net/docs.php
    * php fpm 官方介绍文档： https://www.php.net/manual/en/intro.fpm.php
    * composer: https://getcomposer.org/
    * php-redis: https://github.com/phpredis/phpredis
* REDIS 建议2.8以上
    * redis官方: https://redis.io/
* MYSQL  建议5.6及以上
* Nginx 
    * Nginx 官方社区：http://nginx.org/

### 安装

#### Nginx 增加server配置

配置参考如下

```
    
            server {
                   listen 80 default_server;
           
                   index index.php index.html index.htm;
                   access_log  logs/access.log  main;
                   error_log  logs/error.log;
           
                   root   /var/www/html/web; # 请配置到web/index.php 文件所在的绝对路径。
           
                   if ($request_uri ~ " ") {
                       return 444;
                   }
          
                   location / {
                       try_files $uri $uri/ /index.php?$query_string;
                       log_not_found off;
                   }
           
           
                   location = /favicon.ico {
                       allow all;
                       log_not_found off;
                       access_log off;
                   }
           
                   location = /robots.txt {
                       allow all;
                       log_not_found off;
                       access_log off;
                   }
           
           
                   location ~ /\. {
                       deny all;
                       access_log off;
                       log_not_found off;
                   }
           
           
                   location ~ \.php$ {
                       include fastcgi.conf;
                       fastcgi_pass fpm.localhost:9000;
                       fastcgi_split_path_info ^(.+\.php)(/.+)$;
                       fastcgi_index   index.php;
                       fastcgi_connect_timeout 60;
                       fastcgi_send_timeout 480;
                       fastcgi_read_timeout 480;
                       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
                       fastcgi_intercept_errors off;
                   }
           
           
                 
                   error_page  404              /404.html;
                   error_page   500 502 503 504  /50x.html;
                   location = /50x.html {
                   }
               }
```
               
#### 系统环境变量设置

系统环境变量添加配置 `export QFE_HTTPS_IDC=<你的服务器所在集群>`， 此配置决定了你使用`.ini` 的各个配置文件中的哪个集群的配置
  
         
#### 配置文件修改

* 重命名`./config/default` 为 `./config/ini`。 
* 修改:`./config/ini/db.ini` 相应机房mysql信息.
* 修改:`./config/ini/param.ini` 不同集群配置下机房名称.
* 修改:`./config/ini/cache.ini` && `./config/ini/param.ini` 相应机房redis信息.

#### 安装依赖扩展

进入到 <path>/web/ 下 执行 `composer install` 等待扩展安装完成
    
#### 初始化创建数据库表
    
    数据库表生成: 进入到 <path>/web/ 下 执行 `php yii migrate` 即可使用`ini/db.ini` 中配置的数据库生成基本的表结构。

#### 更改特定文件夹权限

授予web项目根目录下 资源目录:web/assets && 运行时目录:runtime 读写权限
    `chmod -R  777 runtime && chmod -R 777 web/assets`

复制 `web/index.default.php` 命名为 `web/index.php`

#### 启动

启动php-fpm 和 nginx

#### 首次登陆时默认账号密码

用户名：admin

密码：admin
