#!/bin/sh

cd $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE
docker-compose stop
docker-compose rm -f

