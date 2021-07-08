<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\DatabaseTest;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\DatabaseTest\MysqlPersistentConnection
 */
class MysqlPersistentConnectionTest extends TestCase
{
    public function testConstruction(): void
    {
        $connection = new MysqlPersistentConnection();
        $params     = $connection->getConnectionParams();
        $this->assertCount(1, $this->listDatabases($params));

        $connection = new MysqlPersistentConnection();
        $url        = $connection->getConnectionUrl();
        $this->assertCount(1, $this->listDatabases(['url' => $url]));
    }

    /**
     * @depends testConstruction
     */
    public function testDestruction(): void
    {
        $connection = new MysqlPersistentConnection();
        $params     = $connection->getConnectionParams();

        new MysqlPersistentConnection();         // Immediately out of scope.
        $tmp = new MysqlPersistentConnection();  // Still in scope because of variable.

        // No race here because we give the db time to cleanup by adding the $tmp
        // connection and we wait for it to be established.
        //
        // Having two databases now ensures that multiple databases with
        // unique names are created by the connection class.
        $this->assertCount(2, $this->listDatabases($params));

        // If a connection disappears, it should clean up it's databases.
        unset($tmp);
        $this->assertCount(1, $this->listDatabases($params));
    }

    /**
     * Return an array of database names available
     * on the connection. Mysql system databases
     * are filtered from the list.
     *
     * @param string[] $params Doctrine connection parameters
     * @return string[]
     * @throws \Doctrine\DBAL\DBALException
     */
    private function listDatabases(array $params): array
    {
        $doctrine  = DriverManager::getConnection($params);
        $statement = $doctrine->executeQuery('SHOW DATABASES');
        $data      = $statement->fetchAll(\PDO::FETCH_COLUMN);

        return array_filter(
            $data,
            function ($database) {
                switch ($database) {
                    case 'mysql':
                    case 'information_schema':
                    case 'performance_schema':
                    case 'sys':
                        return false;
                    default:
                        return true;
                }
            }
        );
    }
}
