#!/bin/bash

cd $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE
docker-compose stop varnish
docker-compose rm -f varnish
docker-compose up -d varnish
