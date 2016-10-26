#!/bin/bash

#set up motd graphic
cp $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/motd.txt /etc/update-motd.d/30-banner
update-motd

## Set up aliases to stop and start docker
echo "alias dockerstartall='/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/startall.sh'" >> /home/www/.bashrc
echo "alias dockerstopall='/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/stopall.sh'" >> /home/www/.bashrc
echo "alias dockerrestartvarnish='/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/restartvarnish.sh'" >> /home/www/.bashrc
source /home/www/.bashrc

## If a desired Elastic IP is set attempt to assign it if it isn't already in use
if [ -n "$WEBSITE_DESIREDIP" ]
then
    USEDBY=`aws ec2 describe-addresses --public-ips $WEBSITE_DESIREDIP  | grep "InstanceId" | cut -d":" -f2 | cut -d'"' -f2 | uniq`

    if [ ! "$USEDBY" ]
    then
        aws ec2 associate-address --instance-id $EC --public-ip $WEBSITE_DESIREDIP --allow-reassociation
    fi
fi

# sleep for 5s
echo "Sleep for 5s"
sleep 5

service docker restart

echo "start docker containers..."
/bin/sh $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/startall.sh

docker ps -a

chown -R www:www $WEBSITE_PATH

echo "DONE"