#!/bin/bash
#bin/console sylius:install -e prod
bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug
chown -R www-data:www-data /app/var