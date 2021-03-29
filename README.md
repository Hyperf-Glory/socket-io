# v2着手开始重构...期待中...

## 请暂时不要用于生产环境
项目还在开发优化中
## Socket-IO
再次升级，此版本采用hyperf2.x+Vue+Element搭建的分布式Socket-io系统,利用rpc作为注册，鉴权服务,rpc发布到注册中心.利用dao-cloud+docker多容器部署目前已初步搭建完成，待完成系统业务会继续优化，写份教程供大家学习.
此次系统的业务逻辑借鉴[lumen-im](https://github.com/gzydong/LumenIM) 的逻辑用hyperf重写，第一版本求稳定运行上线.第二版本会重新整理业务架构，代码更加优化。更加符合PHP规范化.
# [Socket-IO服务聊天系统](https://github.com/Hyperf-Glory/socket-io)
<p align="center">
    <a href="https://github.com/Hyperf-Glory/socket-io" target="_blank">
        <img src="https://static.jayjay.cn/1496800949298.jpg"/>
    </a>
</p>

[![Php Version](https://img.shields.io/badge/php-%3E=7.2-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.5-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![sl-im License](https://img.shields.io/github/license/hyperf/hyperf.svg?maxAge=2592000)](https://github.com/Hyperf-Glory/socket-io/blob/master/LICENSE)


## 简介
 
[socket-io](https://im.jayjay.cn) 是基于 [Hyperf](https://hyperf.io) 微服务协程框架(Swoole)和 Vue + ElementUI 网页聊天系统 所开发出来的聊天室。

## 体验地址

[Socket-IO](https://im.jayjay.cn)

## 待优化
- SocketIO事件
- rpc服务
- http业务服务分层
- docker直接部署
- 功能完善
## 新增
- 笔记管理
## 功能
2.0
- 基于Swoole Socket-io服务做消息即时推送
- 支持私聊及群聊
- 支持聊天消息类型有文本、代码块、图片及其它类型文件，并支持文件下载
- 支持聊天消息撤回、删除或批量删除、转发消息（逐条转发、合并转发）
- 支持docker部署(后续写搭建教程)
- Rpc服务注册登录鉴权
- Nsq分布式消息中间件
- Mysql提供数据存储功能
- Redis存储聊天关系映射

## Requirement

- [PHP 7.4+](https://github.com/php/php-src/releases)
- [Swoole 4.6+](https://github.com/swoole/swoole-src/releases)
- [Composer](https://getcomposer.org/)
- [Hyperf >= 2.1.x](https://github.com/hyperf/hyperf/releases)



## 单机部署方式

### Composer

```bash
composer install
```

### env配置
```
APP_NAME=skeleton
APP_ENV=dev

DB_DRIVER=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hyperf
DB_USERNAME=root
DB_PASSWORD=
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci
DB_PREFIX=

REDIS_HOST=localhost
REDIS_AUTH=(null)
REDIS_PORT=6379
REDIS_DB=0

CLOUD_REDIS=default

WEBSOCKET_SERVER_IPS = {"ws1":"127.0.0.1","ws2":"127.0.0.2"}
AMQP_HOST=localhost //rabbitmq地址
NSQ_HOST=localhost //nsq地址
CONSUL_HOST=localhost:8500 //consul地址
NSQD_HOST=127.0.0.1:4151 //nsqd地址

//邮箱配置
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM=
MAIL_NAME=
//静态资源地址
IMAGE_URL=http://127.0.0.1:9500
//七牛配置
QINIU_ACCESS_KEY=
QINIU_SECRET_KEY=
QINIU_BUCKET=
QINBIU_DOMAIN=

```

### nginx配置

```bash
# 至少需要一个 Hyperf 节点，多个配置多行
upstream hyperf_chat_http {
    # Hyperf-Chat HTTP Server 的 IP 及 端口
    server 127.0.0.1:9500;
    server 127.0.0.1:1500;
}
upstream hyperf_chat_ws {
    # 设置负载均衡模式为 IP Hash 算法模式，这样不同的客户端每次请求都会与同一节点进行交互
    ip_hash;
    # Hyperf Chat Server 的 IP 及 端口
    server 127.0.0.1:9502;
    server 127.0.0.1:1502;
}
server {
    listen 443 ssl;
    index index.html index.htm;
    server_name xxx.cn;
  error_log /home/wwwlogs/xxxerr.log;
    root /home/wwwroot/hyperf-chat/public;
    ssl_certificate /etc/ssl/xxx.crt;
    # 指定私钥文件路径
    ssl_certificate_key /etc/ssl/xxx.key;
    ssl_protocols        TLSv1.2 TLSv1.1 TLSv1;
        ssl_ciphers   ECDHE-RSA-AES128-GCM-SHA256:ECDHE:ECDH:AES:HIGH:!NULL:!aNULL:!MD5:!ADH:!RC4;
        ssl_prefer_server_ciphers   on;
        ssl_session_timeout 5m;
      index index.php index.html index.htm;
    location / {
        # 将客户端的 Host 和 IP 信息一并转发到对应节点
           proxy_set_header Host $http_host;
           proxy_set_header X-Real-IP $remote_addr;
           proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

               # 转发Cookie，设置 SameSite
           proxy_cookie_path / "/; secure; HttpOnly; SameSite=strict";

               # 执行代理访问真实服务器
           proxy_pass http://hyperf_chat_http;
    }
    location /socket.io {
        # WebSocket Header
         proxy_http_version 1.1;
         proxy_set_header Upgrade websocket;
         proxy_set_header Connection "Upgrade";

         # 将客户端的 Host 和 IP 信息一并转发到对应节点
         proxy_set_header X-Real-IP $remote_addr;
         proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
         proxy_set_header Host $http_host;

         # 客户端与服务端无交互 60s 后自动断开连接，请根据实际业务场景设置
         proxy_read_timeout 60s ;

               # 执行代理访问真实服务器
         proxy_pass http://hyperf_chat_ws;
    }
}
server
{
  # 80端口是http正常访问的接口
  listen 80;
  server_name xxx.cn;
  # 在这里，我做了https全加密处理，在访问http的时候自动跳转到https
  rewrite ^(.*) https://$host$1 permanent;
}

```

### Start

- 挂起

```bash
composer dump-autoload -o
php bin/hyperf.php start
```


## 打赏(你的支持是我最大的动力)


<p align="center">
    <a href="https://github.com/codingheping/hyperf-chat" target="_blank">
        <img src="https://static.jayjay.cn/pay.jpeg"/>
    </a>
</p>


## 联系方式

- WeChat：naicha_1994
- QQ：847050412
- QQ群:658446650

## socket-io欢迎star
[socket-io](https://github.com/Hyperf-Glory/socket-io)

## License

[LICENSE](LICENSE)
