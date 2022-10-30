#!/bin/bash

_UPD=1

# padding handles script being overwritten during updates
# see https://github.com/orgs/RM-Net-DOV-Control-Panel/dashboard

##################################################
##################################################
##################################################
##################################################
##################################################
##################################################
##################################################
##################################################
##################################################
##################################################
##################################################
##################################################

SOURCE=$1
URL=""

if [[ "$SOURCE" == "stable" ]] ; then
	URL="https://github.com/RMNetDOV/Rmnetdov3/archive/refs/heads/master/Rmnetdov-3-stable.tar.gz"
elif [[ "$SOURCE" == "nightly" ]] ; then
	URL="https://github.com/RMNetDOV/Rmnetdov3/archive/refs/heads/master/Rmnetdov-3-nightly.tar.gz"
elif [[ "$SOURCE" == "git-master" ]] ; then
	URL="https://github.com/RMNetDOV/Rmnetdov3/archive/refs/heads/master/Rmnetdov3-master.tar.gz"
else 
	echo "Izberite vir namestitve (stable, nightly, git-master)"
	exit 1
fi

CURDIR=$PWD

cd /tmp

{
if [ -n "${_UPD}" ]
then
    {
        save_umask=`umask`
        umask 0077 \
        && tmpdir=`mktemp -dt "$(basename $0).XXXXXXXXXX"` \
        && test -d "${tmpdir}" \
        && cd "${tmpdir}"
        umask $save_umask
    } || {
        echo 'mktemp failed'
        exit 1
    }

    echo "Prenašanje RM-Net - DOV Control Panel nadgradnja."
    wget -q -O rmnetdov-3.tar.gz "${URL}"
    if [ -f rmnetdov-3.tar.gz ]
    then
        echo "Razpakiranje RM-Net - DOV Control Panel nadgradnja."
        tar xzf RM-Net-DOV-3.tar.gz --strip-components=1
        cd install/
        php -q \
            -d disable_classes= \
            -d disable_functions= \
            -d open_basedir= \
            update.php
        cd /tmp
        rm -rf "${tmpdir}"
    else
        echo "Posodobitve ni mogoče prenesti."
		cd "$CURDIR"
        exit 1
    fi

fi

cd "$CURDIR"
exit 0
}
