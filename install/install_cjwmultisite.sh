#!/usr/bin/env bash

# install app_cjwmultisite (CjwMultiSite Setup) into ezroot

if [ ! -d "app" -o \
     ! -d "src" -o \
     ! -d "vendor" -o \
     ! -d "web" ] ; then
     echo "You seem to be in the wrong directory"
     echo "Place yourself in the eZ Platform root directory and run"
     echo "sh vendor/cjw-network/multisite-bundle/install/install_cjwmultisite.sh"
     exit 1
fi

# cd ezroot/
# sh vendor/cjw-network/multisite-bundle/install/install_cjwpublish.sh

echo ""
echo "### Installing CjwMultiSite Setup"



echo "# Create app_cjwmultisite application folder, not overwriting existing files"
echo "cp -rvn vendor/cjw-network/multisite-bundle/install/ezroot/* ./"

# copy file only if not exists
cp -rvn vendor/cjw-network/multisite-bundle/install/ezroot/* ./

echo "Done"
