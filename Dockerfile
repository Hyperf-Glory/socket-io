# hyperf/hyperf:7.4
#
# @link     https://www.hyperf.io
# @document https://doc.hyperf.io
# @contact  group@hyperf.io
# @license  https://github.com/hyperf/hyperf/blob/master/LICENSE

FROM hyperf/hyperf:7.4-alpine-v3.11-base

LABEL maintainer="Hyperf Developers group@hyperf.io" version="1.0" license="MIT"

ARG SW_VERSION
ARG COMPOSER_VERSION

##
# ---------- env settings ----------
##
ENV SW_VERSION=${SW_VERSION:-"v4.5.7"} \
    COMPOSER_VERSION=${COMPOSER_VERSION:-"2.0.2"} \
    #  install and remove building packages
    PHPIZE_DEPS="autoconf dpkg-dev dpkg file g++ gcc libc-dev make php7-dev php7-pear pkgconf re2c pcre-dev pcre2-dev zlib-dev libtool automake librdkafka-dev protobuf"
RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories
# update
RUN set -ex \
    && apk update \
    # for swoole extension libaio linux-headers
    && apk add --no-cache libstdc++ openssl git bash wget \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libaio-dev openssl-dev \
# download
    && cd /tmp \
    && curl -SL "https://gitee.com/swoole/swoole/repository/archive/${SW_VERSION}?format=tar.gz" -o swoole.tar.gz \
    && ls -alh \
# php extension:swoole
    && cd /tmp \
    && mkdir -p swoole \
    && tar -zxvf swoole.tar.gz -C swoole --strip-components=1 \
    && ln -s /usr/bin/phpize7 /usr/local/bin/phpize \
    && ln -s /usr/bin/php-config7 /usr/local/bin/php-config \
    && ( \
       cd swoole \
       && phpize \
       && ./configure --enable-mysqlnd --enable-openssl --enable-http2 \
       && make -s -j$(nproc) && make install \
      ) \
    && echo "memory_limit=1G" > /etc/php7/conf.d/00_default.ini \
    && echo "opcache.enable_cli = 'On'" >> /etc/php7/conf.d/00_opcache.ini \
    && echo "extension=swoole.so" > /etc/php7/conf.d/50_swoole.ini \
    && echo "swoole.use_shortname = 'Off'" >> /etc/php7/conf.d/50_swoole.ini \
   # install composer
    && wget -nv -O /usr/local/bin/composer https://github.com/composer/composer/releases/download/${COMPOSER_VERSION}/composer.phar \
    && chmod u+x /usr/local/bin/composer
RUN cd /tmp \
    && pecl install rdkafka \
    && echo "extension=rdkafka.so" > /etc/php7/conf.d/rdkafka.ini
RUN cd /tmp \
    && pecl install protobuf \
    && echo "extension=protobuf.so" > /etc/php7/conf.d/protobuf.ini \
    && cd /tmp \
    && pecl install redis \
    && echo "extension=redis.so" > /etc/php7/conf.d/redis.ini
RUN cd /tmp \
    && wget https://github.com/alanxz/rabbitmq-c/archive/v0.10.0.tar.gz \
    && mkdir -p amqp \
    && tar -zxvf v0.10.0.tar.gz - C amqp --strip-components=1 \
    && cd amqp \
    && mkdir build && cd build \
    && cmake .. \
    && cmake --build . --target install
RUN cd /tmp \
    && wget https://pecl.php.net/get/amqp-1.10.2.tgz \
    && tar -zxvf amqp-1.10.2.tgz \
    && cd amqp-1.10.2 \
    && phpize \
    && ./configure --with-librabbitmq-dir=/usr/local \
    && make \
    && make install
    && echo "extension=amqp.so" > /etc/php7/conf.d/amqp.ini
    # php info
RUN php -v \
    && php -m \
    && php --ri swoole \
    && composer \
    # ---------- clear works ----------
    && apk del .build-deps \
    && rm -rf /var/cache/apk/* /tmp/* /usr/share/man /usr/local/bin/php* \
    && echo -e "\033[42;37m Build Completed :).\033[0m\n"
