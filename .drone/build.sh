#!/bin/bash
BUILD="$PWD"
VERSION="$(git rev-parse --short HEAD)"

echo "Started building at $(date) - $(whoami)"

mkdir -p /tests/www

cp -r ./* /tests/www

cd /tests/www

# Update composer
composer self-update

# Install dependencies
composer install --no-interaction --no-progress

cp jbuild.dist.ini jbuild.ini
cp RoboFile.dist.ini RoboFile.ini

# Build package
vendor/bin/robo build --dev

# Copy acceptance yml
cp tests/acceptance.suite.dist.yml tests/acceptance.suite.yml

mkdir /tests/cmc
cp -r dist/current/* /tests/cmc

# It should be already running, but sometimes the phusion script init is not executed
apache2ctl restart
mysqld &

chown -R www-data .
chown -R www-data /tests
