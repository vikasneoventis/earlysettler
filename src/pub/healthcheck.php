<?php

define('BP', dirname(__FILE__).'/../');

$vendorDir       = require BP . 'app/etc/vendor_path.php';
$vendorAutoload  = BP . $vendorDir . '/autoload.php';
$maintenanceFlag = BP . 'var/.maintenance.flag';
$buildFlag       = BP . 'var/.build.flag';

try {
    if (file_exists($buildFlag)) {
        throw new \Exception('Site is still being built');
    }

    if (!file_exists(BP.$vendorDir)) {
        throw new \Exception('Magento doesn\'t exist');
    }

    if (!file_exists($vendorAutoload)) {
        throw new \Exception('Vendor autoload is not found');
    }

    if (!file_exists($maintenanceFlag)) {

        // @TODO: check DB connection
        // @TODO: check we can write to cache
        // @TODO: check we can read from session

        //Check that static assets have been deployed to the public directory
        $deployedVersion = BP . 'pub/static/deployed_version.txt';
        if (!file_exists($deployedVersion)) {
            throw new \Exception('Static assets do not appear to be present');
        }
    }

    echo "OK\n";

} catch (Exception $e) {
    echo $e->getMessage() . "\n";
    echo "FAIL\n";
    throw $e;
}
