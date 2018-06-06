#!/usr/bin/env bash

set -e

back=`pwd`
branch=`git rev-parse --abbrev-ref HEAD`

cd $1
echo `pwd`
git checkout -f && git clean -df

git checkout release-$branch

git pull --rebase origin release

npm run build

cd $back

cp $1/dist/index.html resources/views/index.blade.php
cp -R $1/dist/static/ public/static/

