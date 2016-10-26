#!/bin/sh

# check if script is already running
ALREADY_RUN=`ps aux | grep "/bin/sh /root/nfs-check.sh" | wc -l`
if [[ $ALREADY_RUN -gt 3 ]]; then
        echo $ALREADY_RUN
        exit
fi

# check if media dir is "i/o error"
VALID_DIR=`stat /mnt/web`

if [[ -z $VALID_DIR ]]; then
        umount -fl /mnt/web
        timeout 5 mount -a
fi

# if folder is not mounted, try until it works
NFS_MOUNTED=`mount | grep web`
while [[ -z $NFS_MOUNTED ]]
do
        timeout 5 mount -a
        NFS_MOUNTED=`mount | grep web`
done
