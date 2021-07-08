#!/usr/bin/env bash

# Configure data locations
LOG="$HOME/database_test.log"
MYSQL="sudo mysql -uroot -proot"

exec 3>> "$LOG"

finish() {
    echo "### Cleaning Database $DBNAME" >&3
    echo "DROP DATABASE \`$DBNAME\`;" | $MYSQL >&3
}

# Create the database
DBNAME="$(date +%s%N)"
echo "CREATE DATABASE \`$DBNAME\`;" | $MYSQL >&3

# PHP 7.3 compatibility, does not support caching_sha2_password, use mysql_native_password on a new user instead.
echo "CREATE USER  IF NOT EXISTS 'test'@'localhost' IDENTIFIED WITH mysql_native_password BY 'test';"  | $MYSQL >&3
echo "GRANT ALL PRIVILEGES ON *.* TO 'test'@'localhost';" | $MYSQL >&3
echo "FLUSH PRIVILEGES;" | $MYSQL >&3

echo "### Trap Set" >&3
trap finish EXIT

echo "driver: pdo_mysql, server_version: 5.6, host: localhost, dbname: ${DBNAME}, user: test, password: test"
echo "### Connection params sent" >&3

# Wait on parent process before cleaning up the database
while read -r DATA; do
    sleep .1
done
