FROM php:7.4.3-fpm-alpine3.11 as environment

WORKDIR /var/www/backend/main

COPY --from=composer:1 /usr/bin/composer /usr/local/bin/composer
RUN apk update && \
    apk add --no-cache --virtual .build-deps \
        linux-headers \
        autoconf \
        gcc \
        libc-dev \
        make \
        curl-dev \
        musl-dev \
        libc-dev \
        zlib-dev \
        dpkg dpkg-dev \
        libmemcached-dev \
        cyrus-sasl-dev \
        libzip-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libxml2-dev \
        yaml-dev \
        autoconf \
        gcc  \
        g++ \
        make  \
        re2c \
        perl  \
        perl-error   \
        perl-git  \
        git-perl  \
        wget \
        zip \
        unzip \
        curl \
        m4 \
    \
    \
    && apk add \
        openssh \
        supervisor \
        libmemcached-libs \
        libgcc  \
        libstdc++  \
        libbz2 \
        libmagic  \
        file  \
        binutils  \
        gmp  \
        isl  \
        libgomp  \
        libatomic \
        mpfr4  \
        mpc1  \
        yaml \
        zlib \
        libzip \
        libgd \
        git \
    \
    \
    && (cd /tmp && wget http://pecl.php.net/get/memcached-3.1.5.tgz && tar xf memcached-3.1.5.tgz) \
    && docker-php-ext-configure /tmp/memcached-3.1.5 && docker-php-ext-install /tmp/memcached-3.1.5 \
    \
    \
    && (cd /tmp && wget http://pecl.php.net/get/yaml-2.2.2.tgz && tar xf yaml-2.2.2.tgz) \
    && docker-php-ext-configure /tmp/yaml-2.2.2 && docker-php-ext-install /tmp/yaml-2.2.2  \
    \
    \
    && docker-php-ext-install -j$(nproc) zip   \
    && docker-php-ext-install -j$(nproc) sockets  \
    && docker-php-ext-install -j$(nproc) opcache \
    && docker-php-ext-install -j$(nproc) bcmath  \
    && docker-php-ext-install -j$(nproc) pcntl  \
    && docker-php-ext-install -j$(nproc) curl  \
    && docker-php-ext-install -j$(nproc) pdo  \
    && docker-php-ext-install -j$(nproc) pdo_mysql  \
    \
    \
    && echo http://dl-cdn.alpinelinux.org/alpine/edge/main >> /etc/apk/repositories   \
    && echo http://dl-cdn.alpinelinux.org/alpine/edge/community >> /etc/apk/repositories    \
    && echo http://dl-cdn.alpinelinux.org/alpine/edge/testing >> /etc/apk/repositories  \
    \
    \
    \
    \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*  \
    && rm -rf /tmp/*

FROM environment as builder

COPY composer.json composer.lock ./

RUN composer install -o --no-scripts

FROM environment as final

COPY . .
COPY --from=builder /var/www/backend/main/vendor /var/www/backend/main/vendor
COPY --from=builder /var/www/backend/main/composer.json /var/www/backend/main/composer.json
COPY --from=builder /var/www/backend/main/composer.lock /var/www/backend/main/composer.lock

RUN composer dump-autoload \
    && chmod +x ./bin/console

CMD ["php-fpm"]
EXPOSE 9000
