#!/bin/bash

cd /var/www/easy-price || exit 1

composer install

cd assets/easyprice
npm install
ng build

cd ../../
yarn install
yarn encore dev
php bin/console doctrine:migration:migrate

chmod o+rwx var -R

exec "php-fpm"