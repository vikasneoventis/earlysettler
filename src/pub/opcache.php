<?php

$isPrivateIP = (false === filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE));
if ($isPrivateIP) {
    opcache_reset();
    echo "PHP OPcache has been reset\n";
} else {
    header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL', FILTER_SANITIZE_STRING) . ' 404 Not Found');
    echo "404 - Page Not Found\n";
}
