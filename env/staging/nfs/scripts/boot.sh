#!/bin/bash

#set up motd graphic
cp $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/motd.txt /etc/update-motd.d/30-banner
update-motd

## Attach internal IP address, detach first if already attached to another instance
if [ -n "$NFS_INTERNAL_IP" ]
then
    ENI_ID=`aws ec2 describe-instances --instance-id $EC | grep NetworkInterfaceId |cut -d":" -f2 | cut -d'"' -f2 | uniq`
    EC_OLD=`aws ec2 describe-instances --filters "Name=tag:type,Values=nfs" "Name=tag:env,Values=staging" "Name=instance-state-name,Values=pending,shutting-down,stopping,stopped" | grep InstanceId |cut -d":" -f2 | cut -d'"' -f2 | uniq`

    if [ "" != "$EC_OLD" ]
    then
            ENI_ID_OLD=`aws ec2 describe-instances --instance-id $EC_OLD | grep NetworkInterfaceId |cut -d":" -f2 | cut -d'"' -f2 | uniq`
            aws ec2 unassign-private-ip-addresses --network-interface-id $ENI_ID_OLD --private-ip-addresses $NFS_INTERNAL_IP
    fi

    aws ec2 assign-private-ip-addresses --network-interface-id $ENI_ID --private-ip-addresses $NFS_INTERNAL_IP
    echo "WAIT FOR 5 seconds"
    sleep 5
    service network restart
fi

## Attach appropriate EBS and mount it
EBS=`aws ec2 describe-volumes --filters Name=tag:website,Values=$WEBSITE_NAME Name=tag:type,Values=media Name=tag:env,Values=$WEBSITE_ENV_TYPE | grep VolumeId | cut -d":" -f2 | cut -d'"' -f2 | uniq`
if [ "available" != "$STATUS" ]
then
    aws ec2 detach-volume --volume-id $EBS --force
    echo "WAIT FOR 10 Seconds"
    sleep 10
    STATUS=`aws ec2 describe-volumes --volume-ids $EBS | grep "State" | cut -d":" -f2 | cut -d'"' -f2 | uniq`
fi

while [ "$STATUS" != "available" ]
do
    sleep 5
    STATUS=`aws ec2 describe-volumes --volume-ids $EBS | grep "State" | cut -d":" -f2 | cut -d'"' -f2 | uniq`
done

aws ec2 attach-volume --volume-id $EBS --instance-id $EC --device /dev/xvdf
echo "WAIT FOR 10 Seconds"
sleep 10
STATUS=`aws ec2 describe-volumes --volume-ids $EBS | grep "State" | cut -d":" -f2 | cut -d'"' -f2 | uniq | sed -n '1p'`
while [ "$STATUS" != "attached" ]
do
    sleep 10
    STATUS=`aws ec2 describe-volumes --volume-ids $EBS | grep "State" | cut -d":" -f2 | cut -d'"' -f2 | uniq | sed -n '1p'`
done

mkdir /mnt/web
chown -R www:www /mnt/web
mount /dev/xvdf /mnt/web


# Set up NFS share
yum install nfs-utils nfs-utils-lib -y
chkconfig nfs on
service rpcbind start
service nfs start
service nfslock start

echo "/mnt/web          10.0.0.0/16(rw,sync,no_root_squash,no_subtree_check)" > /etc/exports
exportfs -av
exportfs

#service docker restart

echo "DONE"
