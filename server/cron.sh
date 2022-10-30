#!/bin/bash

PATH=/sbin:/usr/sbin:/bin:/usr/bin:/usr/local/sbin:/usr/local/bin:/usr/X11R6/bin

if [ -f /usr/local/rmnetdov/server/lib/php.ini ]; then
        PHPINIOWNER=`stat -c %U /usr/local/rmnetdov/server/lib/php.ini`
        if [ $PHPINIOWNER == 'root' ] || [ $PHPINIOWNER == 'rmnetdov'  ]; then
                export PHPRC=/usr/local/rmnetdov/server/lib
        fi
fi

cd /usr/local/rmnetdov/server
$(which php) -q \
    -d disable_classes= \
    -d disable_functions= \
    -d open_basedir= \
    /usr/local/rmnetdov/server/cron.php
