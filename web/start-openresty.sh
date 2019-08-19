#!/bin/bash

cp -r /usr/local/openresty/nginx/ /tmp/
exec /usr/local/openresty/nginx/sbin/nginx -c /tmp/nginx/conf/nginx.conf -g 'daemon off;'
