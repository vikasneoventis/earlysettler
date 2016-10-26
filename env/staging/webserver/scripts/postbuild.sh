#!/bin/bash

# Add index-before to for x-forwarded-for and x-forwarded-proto
cp $WEBSITE_PATH/env/$WEBSITE_ENV_TYPE/$WEBSITE_SERVER_TYPE/scripts/index-before.php $WEBSITE_PATH/current/src/index-before.php
