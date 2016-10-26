#!/bin/bash

# Symlink Magento logs to ebs log directory
docker exec php-fpm sh -c "ln -s /mnt/web/log/magento /var/www/current/src/var/log"

# Symlink Magento logs to ebs log directory
docker exec php-fpm sh -c "ln -s /mnt/web/media /var/www/current/src/media"