<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\DatabaseTest;

/**
 * A connection will make sure a (new) test database
 * will exist after construction and will clean up
 * after destruction.
 *
 * The connection url for the database is
 * provided through the getConnectionUrl method.
 */
interface UrlConnectionInterface
{
    /**
     * Doctrine compatible database connection parameters,
     * those could be fed directly into a new Doctrine
     * or Symfony/Doctrine configuration file.
     *
     * @Example mysql://db_user:db_password@127.0.0.1:3306/db_name?server_version=5.6
     *          mysql://db_user@localhost/db_name?unix_socket=/tmp/socket&server_version=5.6
     */
    public function getConnectionUrl(): string;
}
