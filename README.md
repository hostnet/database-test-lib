This package allows you to create throw away test databases.
Each connection class will establish a connection to a test database
on construction and clean up after itself on destruction.

Installation
============

using composer:

`composer require --dev hostnet/database-test-lib`

or add manually to your `composer.json`:

```json
"require-dev" : {
    "hostnet/database-test-lib": "^1.0.0"
}
```

Usage
=====

```php
<?php
use Doctrine\DBAL\DriverManager;
use Hostnet\Component\DatabaseTest\MysqlPersistentConnection;

$connection = new MysqlPersistentConnection();
$params     = $connection->getConnectionParams();
$doctrine   = DriverManager::getConnection($params);
$statement  = $doctrine->executeQuery('SHOW DATABASES');
$databases  = $statement->fetchAll(\PDO::FETCH_COLUMN);

foreach($databases as $database) {
    echo $database . PHP_EOL;
}
```
Connection types
================

At this moment only the `MysqlPersistentConnection` is available.

## MySQL
This connection will start a mysql daemon on your system under your user
and create a database for you to test on. When the connection goes out
of scope, the database will be dropped.

The persistent part means that the daemon will keep running afterwards,
and will be reused by consecutive connections, even between multiple PHP
scripts.

This behaviour is accomplished using bash. This means it will only work
on systems supporting bash and having a mysql daemon or drop-in
replacement installed. No superuser privileges are required.
