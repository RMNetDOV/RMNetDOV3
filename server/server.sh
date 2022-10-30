#!/bin/bash


PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/sbin:/usr/local/bin:/usr/X11R6/bin

. /etc/profile

umask 022

if [ -f /usr/local/rmnetdov/server/lib/php.ini ]; then
        PHPINIOWNER=`stat -c %U /usr/local/rmnetdov/server/lib/php.ini`
        if [ $PHPINIOWNER == 'root' ] || [ $PHPINIOWNER == 'rmnetdov'  ]; then
                export PHPRC=/usr/local/rmnetdov/server/lib
        fi
fi

cd /usr/local/rmnetdov/server
/usr/bin/php -q \
    -d disable_classes= \
    -d disable_functions= \
    -d open_basedir= \
    /usr/local/rmnetdov/server/server.php

cd /usr/local/rmnetdov/security
/usr/bin/php -q \
    -d disable_classes= \
    -d disable_functions= \
    -d open_basedir= \
    /usr/local/rmnetdov/security/check.php
