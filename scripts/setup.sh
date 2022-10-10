#!/bin/bash

echo WARNING: THIS SETUP SHOULD ONLY RUN ON DEVELOPMENT
read -n 1 -s -r -p "Press any key to continue..."

echo Installing packages...
composer install
npm install

echo Compiling webpack...
npm run dev

echo Copying .env.example to .env...
cp .env.example .env

echo Generating app key to .env...
php artisan key:generate

echo Generating symbolic links for files uploading...
php artisan storage:link

echo Migrating database tables and generating faker data...
npm run db:dev

echo Done.
