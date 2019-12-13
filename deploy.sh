#!/usr/bin/env bash

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

cd $DIR
echo "Current Dir: $DIR"
composer install --no-dev

# since we are using redis as our queue driver flushing redis is dangerous
#redis-cli flushdb
php artisan down
php artisan horizon:terminate
php artisan migrate --force
php artisan up
