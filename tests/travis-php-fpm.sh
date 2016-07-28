#!/bin/bash

file="$1"
owner="$2"
phpversion="$3"
sed -e "s,listen = 127.0.0.1:9000,listen = /tmp/php$phpversion-fpm.sock,g" --in-place $file
sed -e "s,;listen.owner = nobody,listen.owner = $owner,g" --in-place $file
sed -e "s,;listen.group = nobody,listen.group = $owner,g" --in-place $file
sed -e "s,;listen.mode = 0660,listen.mode = 0666,g" --in-place $file
sed -e "s,user = nobody,;user = $owner,g" --in-place $file
sed -e "s,group = nobody,;group = $owner,g" --in-place $file
