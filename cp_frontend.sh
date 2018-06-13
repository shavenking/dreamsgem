#!/usr/bin/env bash

set -e

back=`pwd`
branch=`git rev-parse --abbrev-ref HEAD`

cd $1
echo `pwd`
git checkout -f && git clean -df

git checkout release

git pull --rebase origin release

if [[ $branch == master ]];
then
    sed -i -e "s/staging/www/g" build/webpack.prod.conf.js
    sed -i -e "s/vZ08ruaFRkqnDgzWJhnUImmIBtNON19YAzdKWSRF/1iIpsd4MmqEaCtbvvSwovJlRGcDSzQD9xC3BY67H/g" build/webpack.prod.conf.js
fi
npm run build

cd $back

cp $1/dist/index.html resources/views/index.blade.php
cp -R $1/dist/static/ public/static/

