#!/bin/bash

#running meteoserver
php ../src/server.php >> /tmp/meteoServer.log 2>&1 &

#wailt for port opening
sleep 1

#running 433 scanner
rtl_433 -R 19 -F json > /dev/tcp/127.0.0.1/8080 &
