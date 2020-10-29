#!/bin/bash

# This script was adpated from https://docs.docker.com/config/containers/multi-service_container/

# Start PHP-FPM
echo "Attempting to start php-fpm"
php-fpm -D
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start php-fpm: $status"
  exit $status
fi

# Start nginx
echo "Attempting to start nginx (nginx is the strong, silent type. There will be no output if it succeeds)"
nginx
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $status
fi

# Start nullmailer
echo "Attempting to start nullmailer"
if [ ! -p /var/spool/nullmailer/trigger ]; then
  rm -f /var/spool/nullmailer/trigger
  mkfifo /var/spool/nullmailer/trigger
fi
chown mail:root /var/spool/nullmailer/trigger
chmod 0622 /var/spool/nullmailer/trigger
nullmailer-send
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nullmailer: $status"
  exit $status
fi

# Make service runner owner of writable files just before starting services
chown -R www-data.www-data /var/www/web/sites/default/files/

# Naive check runs checks once a minute to see if either of the processes
# exited. The container exits with an error if it detects that either of the
# processes has exited. Otherwise it loops forever, waking up every 60 seconds

while sleep 60; do
  ps aux |grep "php-fpm: master" |grep -q -v grep
  PROCESS_1_STATUS=$?
  ps aux |grep "nginx: master" |grep -q -v grep
  PROCESS_2_STATUS=$?
  ps aux |grep "nullmailer-send" |grep -q -v grep
  PROCESS_3_STATUS=$?

  # If the greps above find anything, they exit with 0 status
  # If they are not both 0, then something is wrong
  if [ $PROCESS_1_STATUS -ne 0 -o $PROCESS_2_STATUS -ne 0 -o $PROCESS_3_STATUS -ne 0 ]; then
    echo "One of the processes has already exited."
    exit 1
  fi
done
