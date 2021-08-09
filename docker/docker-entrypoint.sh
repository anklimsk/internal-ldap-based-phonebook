#!/usr/bin/env bash

set -o pipefail
set +e

# Script trace mode
if [ "${DEBUG_MODE,,}" == "true" ]; then
    set -o xtrace
fi

CAKE_INSTALLER_MARKER_FILE_INSTALLED=app/tmp/installer/installed.txt

if [ ! -f "$CAKE_INSTALLER_MARKER_FILE_INSTALLED" ]; then
    cp app/Config/core.php app/Config/core.php.orig
    cd /var/www/phonebook
    echo $PHONEBOOK_LANGUAGE_ID | ./app/Console/cake CakeInstaller setuilang

    echo "y" | ./app/Console/cake CakeInstaller setsecurkey

    if [ -z "$PHONEBOOK_SECURITY_SALT" ]; then
        sed -i -E "s/Configure::write\('Security.salt', '[A-Za-z0-9]+'\);/Configure::write\('Security.salt', '$PHONEBOOK_SECURITY_SALT'\);/" app/Config/core.php
    fi

    if [ -z "$PHONEBOOK_SECURITY_CIPHER_SEED" ]; then
        sed -i -E "s/Configure::write\('Security.cipherSeed', '[A-Za-z0-9]+'\);/Configure::write\('Security.cipherSeed', '$PHONEBOOK_SECURITY_CIPHER_SEED'\);/" app/Config/core.php
    fi

    if [ -z "$PHONEBOOK_SECURITY_KEY" ]; then
        sed -i -E "s/Configure::write\('Security.key', '[A-Za-z0-9]+'\);/Configure::write\('Security.key', '$PHONEBOOK_SECURITY_KEY'\);/" app/Config/core.php
    fi

    timezone=`echo $PHONEBOOK_TIMEZONE | sed -E "s#/#\\\\\\\\/#"`
    sed -i -E "s/\/\/date_default_timezone_set\('UTC'\);/date_default_timezone_set\('$timezone'\);/" app/Config/core.php
    sed -i -E "s/\/\/Configure::write\('Config.timezone', 'Europe\/Paris'\);/Configure::write\('Config.timezone', '$timezone'\);/" app/Config/core.php

    echo $PHONEBOOK_BASEURL | ./app/Console/cake CakeInstaller setbaseurl

    cp app/Config/database.php app/Config/database.php.orig
    export DOLLAR=$
    cat /var/www/phonebook/app/Config/database.php.template | envsubst > /var/www/phonebook/app/Config/database.php

    # sleep a while so the DB is ready
    echo "Waiting $PHONEBOOK_INIT_WAIT_CREATEDB seconds before initalizing DB"
    sleep $PHONEBOOK_INIT_WAIT_CREATEDB
    echo -e "n\ny\nn\ny\nn\ny\nn\ny\n" | ./app/Console/cake CakeInstaller createdb

    # date +"%D %X" > "$CAKE_INSTALLER_MARKER_FILE_INSTALLED
    echo "y" | ./app/Console/cake CakeInstaller install
fi

docker-php-entrypoint "$@"
