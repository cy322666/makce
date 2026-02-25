#!/bin/bash

chmod -R 777 storage
chmod -R 777 vendor

composer install

php artisan telescope:install

php artisan serve