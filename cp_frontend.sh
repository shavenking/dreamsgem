#!/usr/bin/env bash

set -e

back=`pwd`
branch=`git rev-parse --abbrev-ref HEAD`

cd $1
echo `pwd`
git checkout -f && git clean -df

git checkout master && git pull --rebase origin master

if [[ $branch == master ]];
then
    sed -i -e "s/staging.dreamsgemdragon.com/dream.jjlinpai.com/g" build/webpack.prod.conf.js
    sed -i -e "s/vZ08ruaFRkqnDgzWJhnUImmIBtNON19YAzdKWSRF/1iIpsd4MmqEaCtbvvSwovJlRGcDSzQD9xC3BY67H/g" build/webpack.prod.conf.js
fi
npm run build

cd $back

rm -rf resources/views/index.blade.php
rm -rf public/static

cp $1/dist/index.html resources/views/index.blade.php
cp -R $1/dist/static public/static

