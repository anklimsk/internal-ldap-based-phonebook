ARG PHP_VERSION=7.4
FROM php:${PHP_VERSION}-fpm

ARG PHONEBOOK_VERSION=1.0.10

RUN apt-get update -yqq \
    && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libldap2-dev \
        zlib1g-dev \
        libicu-dev \
        libzip-dev \
        g++ \
    # lets mark some packages as manually installed, so they wont be removed by accident
    && apt-mark manual libzip4 \
        libpng16-16 \
        libmcrypt4 \
        libjpeg62-turbo \
        libfreetype6 \
        libcurl3-gnutls \
    && docker-php-ext-configure ldap --with-libdir=lib/x86_64-linux-gnu/ \
    && docker-php-ext-install -j$(nproc) ldap \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl \
    && docker-php-ext-configure zip \
    && docker-php-ext-install -j$(nproc) zip \
    && docker-php-ext-install -j$(nproc) bcmath \
    && docker-php-ext-install -j$(nproc) calendar \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-install -j$(nproc) pdo_mysql \
    && docker-php-ext-install -j$(nproc) mysqli \
    && docker-php-ext-install -j$(nproc) gettext \
    && docker-php-ext-install -j$(nproc) exif \
    && docker-php-ext-configure opcache \
    && docker-php-ext-install -j$(nproc) opcache \
    && apt-get remove -y libfreetype6-dev \
        libjpeg62-turbo-dev \
        libmcrypt-dev \
        libpng-dev \
        libldap2-dev \
        zlib1g-dev \
        libicu-dev \
        libzip-dev \
        freetype2-doc \
        g++-8 \
        icu-devtools \
        libhashkit-dev \
        libltdl-dev \
        libpng-tools \
        libsasl2-dev \
        libstdc++-8-dev \
        libltdl-dev \
        g++ \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-source delete

RUN apt-get update -yqq \
    # gettext-base for envsubst, propcs needed by installer
    && apt-get install -y gettext-base procps \
    && rm -rf /var/lib/apt/lists/*

ADD https://github.com/anklimsk/internal-ldap-based-phonebook/archive/refs/tags/v$PHONEBOOK_VERSION.tar.gz /tmp/phonebook.tar.gz
ADD https://getcomposer.org/download/2.1.5/composer.phar /usr/bin/composer

 # 1=english, 2=russian
ENV PHONEBOOK_LANGUAGE_ID=1 \
    PHONEBOOK_TIMEZONE=UTC \
    PHONEBOOK_INIT_WAIT_CREATEDB=180

ENV PHONEBOOK_SECURITY_KEY= \
    PHONEBOOK_SECURITY_SALT= \
    PHONEBOOK_SECURITY_CIPHER_SEED=

ENV PHONEBOOK_BASEURL=http://localhost:8080

ENV PHONEBOOK_LDAP_PERSISTENT=false \
    PHONEBOOK_LDAP_HOST= \
    PHONEBOOK_LDAP_PORT=389 \
    PHONEBOOK_LDAP_LOGIN= \
    PHONEBOOK_LDAP_PASSWORD= \
    PHONEBOOK_LDAP_BASEDN= \
    PHONEBOOK_LDAP_TYPE=ActiveDirectory \
    PHONEBOOK_LDAP_TLS=true \
    PHONEBOOK_LDAP_VERSION=3

ENV PHONEBOOK_DB_TYPE=Mysql \
    PHONEBOOK_DB_PERSISTENT=false \
    PHONEBOOK_DB_HOST= \
    PHONEBOOK_DB_PORT=3306 \
    PHONEBOOK_DB_LOGIN= \
    PHONEBOOK_DB_PASSWORD= \
    PHONEBOOK_DB_DATABASE=phonebook \
    PHONEBOOK_DB_SCHEMA= \
    PHONEBOOK_DB_TABLE_PREFIX= \
    PHONEBOOK_DB_ENCODING=utf8

RUN mkdir -p /var/www/phonebook \
    && tar -xvz --directory /var/www/phonebook -f /tmp/phonebook.tar.gz --strip 1 \
    && rm -f /tmp/phonebook.tar.gz \
    && chmod +x /usr/bin/composer \
    && cd /var/www/phonebook \
    && composer install --optimize-autoloader --no-dev --prefer-dist \
    && rm -f /usr/bin/composer \
    && ./app/Console/cake CakeInstaller check \
    && ./app/Console/cake CakeInstaller setdirpermiss \
    && ./app/Console/cake CakeInstaller createsymlinks \
    && chown -R www-data:www-data /var/www/phonebook/app/tmp \
    && chown -R www-data:www-data /var/www/phonebook/app/Config \
    && rm -f /var/www/phonebook/app/webroot/test.php

COPY ./docker/docker-entrypoint.sh /usr/local/bin/docker-phonebook-entrypoint
RUN chmod +x /usr/local/bin/docker-phonebook-entrypoint
COPY ./docker/database.php.template /var/www/phonebook/app/Config/database.php.template

RUN mkdir -p /tmp/patch
COPY ./docker/*.patch /tmp/patch/
RUN cd /var/www/phonebook/ \
    && patch app/Config/cakeinstaller.php /tmp/patch/cakeinstaller.patch \
    && rm -rf /tmp/patch

# Build assets
RUN mkdir -p /usr/share/man/man1 \
    && apt-get update -yqq \
    && apt-get install -y default-jre \
    && cd /var/www/phonebook/ \
    && ./app/Console/cake asset_compress build \
    && apt-get remove -y default-jre \
    && chown -R www-data:www-data /var/www/phonebook/app/tmp \
    && rm -rf /var/lib/apt/lists/*

USER www-data
WORKDIR /var/www/phonebook/
ENTRYPOINT ["/usr/local/bin/docker-phonebook-entrypoint"]
CMD ["php-fpm"]
