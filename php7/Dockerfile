# VERSION alpine-3.8
# Author: lizhy
FROM alpine:3.8

MAINTAINER lizhy woody712@gmail.com

RUN echo "http://mirrors.aliyun.com/alpine/v3.8/main" > /etc/apk/repositories && \
    echo "http://mirrors.aliyun.com/alpine/v3.8/community" >> /etc/apk/repositories && \
    apk add --update bash tzdata gcc g++ libc-dev make re2c yaml-dev zlib-dev nghttp2-dev hiredis-dev libxml2-dev postgresql-dev protobuf composer && \
    cp -r -f /usr/share/zoneinfo/Hongkong /etc/localtime && \
    apk add php7-dev php7-fpm php7-bcmath php7-bz2 php7-calendar php7-ctype php7-curl \
            php7-dom php7-enchant php7-exif php7-fileinfo php7-ftp php7-gd \
            php7-gettext php7-iconv php7-imagick-dev php7-imagick php7-json php7-mbstring \
            php7-mcrypt php7-mysqli php7-mysqlnd php7-phar \
            php7-openssl php7-pcntl php7-pdo_mysql php-gmp \
            php7-posix php7-redis php7-pear php7-opcache \
            php7-session php7-sockets php7-simplexml php7-zip \
            php7-sysvmsg php7-sysvsem php7-sysvshm php7-tokenizer php7-wddx php7-xml \
            php7-xmlreader php7-xmlwriter php7-xsl php7-zlib php7-yaml php7-pgsql

RUN pecl install http://pecl.php.net/get/msgpack-2.0.3.tgz && \
    pecl install http://pecl.php.net/get/igbinary-2.0.8.tgz && \
    pecl install http://pecl.php.net/get/protobuf-3.6.1.tgz

COPY cphalcon /home/cphalcon
RUN cd /home/cphalcon/build && ./install

COPY swoole /home/swoole
RUN cd /home/swoole && phpize && ./configure --enable-sockets --enable-openssl --enable-async-redis \
    --enable-mysqlnd --enable-coroutine-postgresql --enable-debug-log --enable-trace-log --enable-http2 && make clean && make && make install && \
    cp /home/swoole/modules/swoole.so /usr/lib/php7/modules/

WORKDIR /etc/php7/conf.d
RUN echo "extension=igbinary.so" > igbinary.ini && \
    echo "extension=msgpack.so" > msgpack.ini && \
    echo "extension=phalcon.so" > phalcon.ini && \
    echo "extension=swoole.so" > swoole.ini && \
    echo "extension=protobuf.so" > protobuf.ini

RUN rm -rf /home/swoole && \
    rm -rf /home/cphalcon

COPY php.ini /etc/php7/php.ini
# replace the default fpm.conf
COPY php-fpm.conf /etc/php7/php-fpm.conf
# replace the default fpm pool conf
COPY www.conf /etc/php7/php-fpm.d/www.conf

RUN mkdir -p /data/web && mkdir -p /data/logs
VOLUME /data/web
VOLUME /data/logs
EXPOSE 9000
EXPOSE 8080

CMD ["php-fpm7"]
