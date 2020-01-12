#!/bin/bash

scriptDir=$(dirname -- "$(readlink -f -- "$BASH_SOURCE")")

#running meteoserver
php $scriptDir/../src/server.php >> /tmp/meteoServer.log 2>&1 &

PID=$!
echo $PID > /var/lock/meteoServer.pid

#wailt for port opening
sleep 1

#running 433 scanner
/usr/local/bin/rtl_433 -R 19 -F json > /dev/tcp/127.0.0.1/8080 &

PID=$!
echo $PID > /var/lock/meteoServerScanner.pid
