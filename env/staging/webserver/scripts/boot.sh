#!/bin/bash

#set up motd graphic
cp $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/motd.txt /etc/update-motd.d/30-banner
update-motd

# Set up aliases to stop and start docker
echo "alias dockerstartall='/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/startall.sh'" >> /home/www/.bashrc
echo "alias dockerstopall='/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/stopall.sh'" >> /home/www/.bashrc
echo "alias magerun='docker exec php-fpm magerun --root-dir=/var/www/current/src'" >> /home/www/.bashrc
source /home/www/.bashrc

# Attempt to assign an elastic IP if it isn't already in use
if [ -n "$WEBSITE_DESIREDIP" ]
then
    USEDBY=`aws ec2 describe-addresses --public-ips $WEBSITE_DESIREDIP  | grep "InstanceId" | cut -d":" -f2 | cut -d'"' -f2 | uniq`

    if [ ! "$USEDBY" ]
    then
        # Get first IP into one webserver
        aws ec2 associate-address --instance-id $EC --public-ip $WEBSITE_DESIREDIP --allow-reassociation
    else
        # Get second IP into the other webserver
        USEDBY_2=`aws ec2 describe-addresses --public-ips $WEBSITE_DESIREDIP_2  | grep "InstanceId" | cut -d":" -f2 | cut -d'"' -f2 | uniq`
        if [ ! "$USEDBY_2" ]
        then
            aws ec2 associate-address --instance-id $EC --public-ip $WEBSITE_DESIREDIP_2 --allow-reassociation
        fi
    fi
fi

# Install nfs client and set up fstab to mount media volume
yum install nfs-utils nfs-utils-lib -y
echo "$NFS_INTERNAL_IP  nfs" >> /etc/hosts
echo "nfs:/mnt/web /mnt/web nfs4 timeo=3,retrans=3,actimeo=10,retry=3,soft,intr,rsize=32768,wsize=32768 0 0" >> /etc/fstab
mkdir /mnt/web

# Create magento directory in EBS
echo "Creating magento logs inside /mnt/web/log"
dir=/mnt/web/log/magento
if [[ ! -d $dir ]]; then
    mkdir -p $dir
    chown -R www:www $dir
    echo "magento logs created successfully!"
else
    echo "magento directory of logs already exists!"
fi

mount -a

# Set up cron to check nfs status
echo "* * * * * /bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/nfs-check.sh" >> /var/spool/cron/root

# This is required to mount the EBS directory as a volume in docker
service docker restart

# logrotate *requires* root ownership for security
chown 0:0 $WEBSITE_PATH/env/common/config/logrotate/logrotate.conf;
chmod 600 $WEBSITE_PATH/env/common/config/logrotate/logrotate.conf;

# Start docker containers (alias not working on boot)
echo "start docker containers..."
/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/startall.sh

# Set up Magento cron job
echo "* * * * * ! test -e $WEBSITE_PATH/current/src/maintenance.flag && docker exec php-fpm runuser -l www -c '/bin/bash /var/www/current/src/scheduler_cron.sh --mode always'" >> /var/spool/cron/www
echo "* * * * * ! test -e $WEBSITE_PATH/current/src/maintenance.flag && docker exec php-fpm runuser -l www -c '/bin/bash /var/www/current/src/scheduler_cron.sh --mode default'" >> /var/spool/cron/www

# Cron to reindex all
echo "0 2 * * *  ! test -e $WEBSITE_PATH/current/src/maintenance.flag && docker exec php-fpm runuser -l www -c 'php /var/www/current/src/shell/indexer.php --reindexall'" >> /var/spool/cron/www

docker ps -a

# sleep 10s in any case if the docker containers are not yet initialized fully.
echo "WAIT FOR 10 Seconds in case containers are not ready"
sleep 10

# run symlinks
/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/symlinks.sh


# Add correct permissions
chown -R www:www $WEBSITE_PATH
chown -R www:www /mnt/web

echo "DONE"
