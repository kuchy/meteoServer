# meteoServer

Simple php server for saving local 433Mhz temperature sensor to mysql DB and uload data to wunderground PWS ussing old Realtek RTL2832 based DVB dongles and unix pc.

How to use
* install/build [rtl_433](https://github.com/merbanan/rtl_433/)
* clone repo
* composer install
* rtl_433 needs to be restarted sometimes
 `cp ./etc/meteoServer /etc/cron.d/meteoServer`
* ./bin/startup.sh






  

