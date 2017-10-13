#!/usr/bin/env bash

# Configure data locations
DATA_DIR="/tmp/repository_test_mysql_data_$USER"
SOCKET="/tmp/repository_test_mysql_socket_$USER"
LOG="$HOME/.repository_test_mysql_log_$USER"
MYSQLD='/usr/sbin/mysqld'

exec 3>> "$LOG"

finish() {
    echo "### Cleaning Database $DBNAME" >&3
    echo "DROP DATABASE \`$DBNAME\`;" | mysql --socket="$SOCKET" -u root --password=
    (
        flock -n 9 || exit 0
        find "$LOG" -size +1M -exec echo "### rotating {}" \; >&3
        exec 3>&-
        find "$LOG" -size +1M | xargs savelog -q -n -t -c3
        echo "FLUSH LOGS;" | mysql --socket="$SOCKET" -u root --password=
    ) 9>/var/lock/repository_rotate_${USER}.lock
}

# Setup database server if not already running
if [ ! -S "$SOCKET" ]; then
    if [ -d "$DATA_DIR" ]; then
        rm -rf "$DATA_DIR"
    fi
    mkdir -p "$DATA_DIR"
    mysql_install_db  --no-defaults --datadir="$DATA_DIR" &>> "$LOG"
    ${MYSQLD} --no-defaults --skip-log-bin --general-log=true --general-log-file="$LOG" --datadir="$DATA_DIR" --skip-networking --socket="$SOCKET" >&3 2>&3 &
    N=0
    while [ ! -S "$SOCKET" ]; do
        echo -n '.' >&3
        if [ $((N+=1)) -gt 100 ]; then
            echo >&3
            echo '### Could not start mysqld, timeout reached' >&3
            exit 1
        fi
        sleep 0.1
    done
    echo "FLUSH LOGS;" | mysql --socket="$SOCKET" -u root --password=
    echo '### connected' >&3
fi

# Create the database
if [ -x uuidgen ]; then
    DBNAME="$(uuidgen)"
else
    # An UUID is obviously better, but the tool might not be available.
    DBNAME="$(date +%s%N)"
fi
echo "CREATE DATABASE \`$DBNAME\`;" | mysql --socket="$SOCKET" -u root --password= >&3

echo "### Trap Set" >&3
trap finish EXIT

echo "driver: pdo_mysql, server_version: 5.6, unix_socket: ${SOCKET},,dbname: ${DBNAME}, user: root"
echo "### Connection params sent" >&3

# Wait on parent process before cleaning up the database
while read -r DATA; do
    sleep .1
done
