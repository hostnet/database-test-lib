<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
namespace Hostnet\Component\DatabaseTest;

/**
 * A connection will make sure a (new) test database
 * will exist after construction and will clean up
 * after destruction.
 *
 * The connection parameters for the database are
 * provided through the getConnectionParams method.
 */
interface ConnectionInterface
{
    /**
     * Doctrine compatible database connection parameters,
     * those could be fed directly into a new Doctrine
     * connection.
     *
     * @return array
     */
    public function getConnectionParams();
}
