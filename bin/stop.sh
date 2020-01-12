#!/bin/bash

METEO_SERVER_PID=/var/lock/meteoServer.pid
if test -f "$METEO_SERVER_PID"; then
	echo "meteoserver pid exist stopping..."
	PID=$(cat $METEO_SERVER_PID)
	kill -9  $PID
	rm $METEO_SERVER_PID;
fi

METEO_SERVER_SCANNER_PID=/var/lock/meteoServerScanner.pid
if test -f "$METEO_SERVER_SCANNER_PID"; then
        echo "meteoserver-scanner pid exist stopping..."
        PID=$(cat $METEO_SERVER_SCANNER_PID)
        kill -9  $PID
	rm $METEO_SERVER_SCANNER_PID
fi

echo "all processes stopped"
