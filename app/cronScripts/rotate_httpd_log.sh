#!/bin/sh

#Set path for systems that do not have path defined for root
PATH=/bin:/usr/bin:/usr/local/bin:/sbin:/usr/sbin:/usr/local/sbin
export PATH

#The backup-to-be-made's file name

FILE=/var/log/httpd/${PROD_URL}-access_log.2.gz
DEST=/opt/httpd_logs_old/${PROD_URL}-access_log_$(date +%Y%m%d).gz
mv "${FILE}" "${DEST}"
exit

