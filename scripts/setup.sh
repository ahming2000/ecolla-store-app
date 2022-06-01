#!/bin/bash

echo Installing packages...
composer install
npm install

echo Copying .env.example to .env...
cp .env.example .env

echo Generating app key to .env...
php artisan key:generate

echo Generating symbolic links for files uploading...
php artisan storage:link

echo Migrating database tables and generating faker data...
npm run db:fresh

echo Done.


