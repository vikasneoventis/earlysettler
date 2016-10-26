#!/usr/bin/env bash

function postbuildEnv () {
    # Set up git repo
    echo "Setting up git repo for env..."
    cd $WEBSITE_PATH/env;
    rm -rf ./.git;
    git init;
    git add .;
    git commit --quiet --message "Initial commit";
}



function postbuildApp () {
    # Latest Release Directory
    cd $WEBSITE_PATH/releases;
    latestRelease=`ls -r1 | head -1`;


    # Set up git repo
    echo "Setting up git repo for app..."
    cd $WEBSITE_PATH/releases/$latestRelease/src;
    git init;
    gitIgnore="
    /var
    /pub/media
    /pub/static
    ";
    printf "$gitIgnore" > .gitignore;
    git add .;
    git commit --quiet --message "Initial commit";


    # Run DB upgrades
    chown -R www:www $WEBSITE_PATH/releases/$latestRelease;
    docker exec -u www php-fpm /usr/bin/env php /var/www/releases/$latestRelease/src/bin/magento maintenance:enable;
    docker exec -u www php-fpm /usr/bin/env php /var/www/releases/$latestRelease/src/bin/magento setup:upgrade > $WEBSITE_PATH/status.txt;
    docker exec -u www php-fpm /usr/bin/env php /var/www/releases/$latestRelease/src/bin/magento maintenance:disable >> $WEBSITE_PATH/status.txt;

# Get var/di , generation and view_processed
aws s3 cp s3://$WEBSITE_BUCKET/compiled_code.tgz $WEBSITE_PATH/releases/$latestRelease/src/var/;
cd $WEBSITE_PATH/releases/$latestRelease/src/var/;
nice tar xzf compiled_code.tgz -C .;
rm -rf compiled_code.tgz;

# Get static/
aws s3 cp s3://$WEBSITE_BUCKET/static_code.tgz $WEBSITE_PATH/releases/$latestRelease/src/pub/static;
cd $WEBSITE_PATH/releases/$latestRelease/src/pub/static;
nice tar xzf static_code.tgz -C .;
rm -rf static_code.tgz;


    # Move Symlinks
    cd $WEBSITE_PATH;
    cat status.txt;
    if grep -q Exception "status.txt"; then
        echo "Error : There is some issue with the previous step !";
    else
        if [ -d "releases/$latestRelease" ]; then
            echo "Set up new symlink for current";
            cd $WEBSITE_PATH;
            rm -f ./current;
            ln -sf releases/$latestRelease current;

            # Remove old releases keeping 5
            cd $WEBSITE_PATH/releases;
            lastReleases=`ls -r1 | awk 'NR>5'`;
            if [ ! -z "$lastReleases" ]; then
                rm -rf `ls -r1 | awk 'NR>5'`;
                echo "$lastReleases were removed successfully!";
            else
                echo "There are no more than 5 releases";
            fi

            echo "Symlinking Revisions..";
            cd $WEBSITE_PATH/releases/$latestRelease/src;
            rm -f ./REVISION.txt;
            ln -sf ../REVISION.txt REVISION.txt;

            # Run Symlinks for persisten logs & media
            /bin/bash $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/symlinks.sh;

            echo "All Successful!";
        fi
    fi

    cd $WEBSITE_PATH;
    rm -rf status.txt;


    # Clear PHP OPcache
    curl -i http://localhost/opcache.php;


    # Ping frontend & backend to force some setup scripts to run
    /usr/bin/env bash $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/ping-site.sh;
}



# Set correct ownership & permissions
/usr/bin/env bash $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/set-permissions.sh;


deploymentType=${1:-};
case "${deploymentType}" in
    "env")
        echo "Deployment type 'env' selected";
        echo "Post-building Env...";
        postbuildEnv;
        ;;
    "magento")
        echo "Deployment type 'magento' selected";
        echo "Post-building App...";
        postbuildApp;
        ;;
    *)
        echo "Deployment type 'both' selected";
        echo "Post-building App...";
        postbuildApp;
        echo "Post-building Env...";
        postbuildEnv;
        ;;
esac

# Set correct ownership & permissions again
/usr/bin/env bash $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/set-permissions.sh;
chown -R www:www $WEBSITE_PATH/current;
chown -R www:www $WEBSITE_PATH/env;
chown -R www:www $WEBSITE_PATH/releases/$latestRelease/;

