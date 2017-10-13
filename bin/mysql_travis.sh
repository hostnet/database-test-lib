#!/usr/bin/env bash

# Configure data locations
LOG="$HOME/database_test.log"

exec 3>> "$LOG"

finish() {
    echo "### Cleaning Database $DBNAME" >&3
    echo "DROP DATABASE \`$DBNAME\`;" | mysql >&3
}

# Create the database
DBNAME="$(date +%s%N)"
echo "CREATE DATABASE \`$DBNAME\`;" | mysql >&3

echo "### Trap Set" >&3
trap finish EXIT

echo "driver: pdo_mysql, server_version: 5.6, host: localhost, dbname: ${DBNAME}, user: root"
echo "### Connection params sent" >&3

# Wait on parent process before cleaning up the database
while read -r DATA; do
    sleep .1
done
