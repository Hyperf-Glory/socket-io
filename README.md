## Hyperf-Chat-Upgrade
再次升级，此版本采用hyperf2.x+Vue+Element搭建的分布式Socket-io系统,利用rpc作为注册，鉴权服务,rpc发布到注册中心.准备采用dao-cloud+docker部署目前已初步搭建完成，待完成系统业务会继续优化，写份教程供大家学习.
此次系统的业务逻辑借鉴[lumen-im](https://github.com/gzydong/LumenIM) 的逻辑用hyperf重写，第一版本求稳定运行上线.第二版本会重新整理业务架构，代码更加优化。更加符合PHP规范化.
# [Hyperf-Chat服务聊天系统](https://github.com/codingheping/hyperf-chat-upgrade)
<p align="center">
    <a href="https://github.com/codingheping/hyperf-chat-upgrade" target="_blank">
        <img src="https://static.jayjay.cn/1496800949298.jpg"/>
    </a>
</p>

[![Php Version](https://img.shields.io/badge/php-%3E=7.2-brightgreen.svg?maxAge=2592000)](https://secure.php.net/)
[![Swoole Version](https://img.shields.io/badge/swoole-%3E=4.5-brightgreen.svg?maxAge=2592000)](https://github.com/swoole/swoole-src)
[![sl-im License](https://img.shields.io/github/license/hyperf/hyperf.svg?maxAge=2592000)](https://github.com/komorebi-php/hyperf-chat/blob/master/LICENSE)


## 简介
 
[hyperf-chat-upgrade](https://im.jayjay.cn) 是基于 [Hyperf](https://hyperf.io) 微服务协程框架(Swoole)和 Vue + ElementUI 网页聊天系统 所开发出来的聊天室。

## 体验地址

暂未开放

## 功能
1.0
- 基于Swoole Socket-io服务做消息即时推送
- 支持私聊及群聊
- 支持聊天消息类型有文本、代码块、图片及其它类型文件，并支持文件下载
- 支持聊天消息撤回、删除或批量删除、转发消息（逐条转发、合并转发）
- 支持docker部署(后续写搭建教程)
- Rpc服务注册登录鉴权
- Nsq分布式消息中间件
- Mysql提供数据存储功能
- Redis存储聊天关系映射
### 问题
Json-Rpc 业务架构比较混乱和Service层架构冲突。下个版本着重优化，把HTTP和Rpc部分业务分到Service层.
代码规范不符合现代化.有重复的代码使用.socket-io服务单独重构独立成为分布式服务


2.0
- 重新架构
- 代码更符合PHP标准化

## Requirement

- [PHP 7.2+](https://github.com/php/php-src/releases)
- [Swoole 4.5+](https://github.com/swoole/swoole-src/releases)
- [Composer](https://getcomposer.org/)
- [Hyperf >= 2.x](https://github.com/hyperf/hyperf/releases)



## 单机部署方式

### Composer

```bash
composer update
```

### env配置

`vim .env`

```bash
WS_URL=wss://im.jayjay.cn/im
STORAGE_IMG_URL=$host/storage/upload/
STORAGE_FILE_URL=$host/file/upload/
APP_URL=https://im.jayjay.cn
WEB_RTC_URL=wss://im.jayjay.cn/video
```
### nginx配置

```bash
server{
    listen 80;
    server_name im.jayjay.cn;
    return 301 https://$server_name$request_uri;
}

server{
    listen 443 ssl;
    root /data/wwwroot/;
    add_header Strict-Transport-Security "max-age=31536000";
    server_name xxx;
    access_log /data/wwwlog/xxx.access.log;
    error_log /data/wwwlog/xxx.error.log;
    client_max_body_size 100m;
    ssl_certificate /etc/nginx/ssl/full_chain.pem;
    ssl_certificate_key /etc/nginx/ssl/private.key;
    ssl_session_timeout 5m;
    ssl_protocols TLSv1.1 TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES128-GCM-SHA256:HIGH:!aNULL:!MD5:!RC4:!DHE;
    location / {
        proxy_pass http://127.0.0.1:9500;
        proxy_set_header Host $host:$server_port;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Real-PORT $remote_port;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
   
    ## 此处可配置负载，详情百度
    location /socket-io/ {
        proxy_pass http://127.0.0.1:9502;
        proxy_http_version 1.1;
        proxy_read_timeout   3600s;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
     
    location ~ .*\.(js|ico|css|ttf|woff|woff2|png|jpg|jpeg|svg|gif|htm)$ {
        root xxx;
    }
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

## hyperf-chat欢迎star
[hyperf-chat](https://github.com/codingheping/hyperf-chat)

## License

[LICENSE](LICENSE)
