# Oauth 2.0 登录配置

本系统支持第三方OAuth2登录配置

配置文件 `param.ini` 里, 增加

```text
auth2.name = <请自行定义登录方式的名称，eg: democlient>
auth2.client_id = <client_id 需要前往OAuth2 服务提供方申请>
auth2.client_secret = <client_secret 需要前往OAuth2 服务提供方申请>
auth2.auth_url = <认证url， 需要前往OAuth2 服务提供方申请>
auth2.token_url = <获取token url， 需要前往OAuth2 服务提供方申请>
auth2.api_base_url = <获取用户信息url， 需要前往OAuth2 服务提供方申请>
auth2.attribute_map.name = <用户名，OAuth2 授权中获取用户信息`api_base_Url`接口对应的字段>
auth2.attribute_map.email = <邮箱地址，OAuth2 授权中获取用户信息`api_base_Url`接口对应的字段>
auth2.img_url = <OAuth2 服务提供方logo，支持远程url，或者放置在web/目录下> 
```

以上是针对OAuth2的通用登录配置

我们也尝试制作了一个针对github登录的配置样例，如下

```text
auth2.name = github ;
auth2.client_id = "e2dbd5d8a4c690303XXX"
auth2.client_secret = "c4112f4ceacc5589cc4d370865457a01f562XXXX"
auth2.auth_url = "https://github.com/login/oauth/authorize"
auth2.token_url = "https://github.com/login/oauth/access_token"
auth2.api_base_url = "https://api.github.com"

;github 返回的login字段，对应本系统的name字段
auth2.attribute_map.name = "login" 

; github 返回的email字段对应 本系统中email字段
auth2.attribute_map.email = "email" 

; 此处使用的是web/ 目录下的github.png 文件，也可以使用url,如:http://your.host.com/icon.png
auth2.img_url = "/github.png"  
```

github OAuth2 配置的相关文档请参考

[Authorizing OAuth Apps](https://developer.github.com/apps/building-oauth-apps/authorizing-oauth-apps/)



