#!/bin/sh
set -e

# Check if PHP-FPM is running
if ! pgrep -x "php-fpm" > /dev/null; then
    exit 1
fi

# Check if PHP-FPM pool is responding
if ! cgi-fcgi -bind -connect 127.0.0.1:9000 > /dev/null 2>&1; then
    exit 1
fi

exit 0
