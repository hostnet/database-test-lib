<?php
namespace Hostnet\Component\DatabaseTest;

use Doctrine\DBAL\DriverManager;

/**
 * @covers Hostnet\Component\DatabaseTest\MysqlPersistentConnection
 */
class MysqlPersistentConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruction()
    {
        $connection = new MysqlPersistentConnection();
        $params     = $connection->getConnectionParams();
        $this->assertEquals(1, count($this->listDatabases($params)));
    }

    /**
     * @depends testConstruction
     */
    public function testDestruction()
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
        $this->assertEquals(2, count($this->listDatabases($params)));

        // If a connection disappears, it should clean up it's databases.
        unset($tmp);
        $this->assertEquals(1, count($this->listDatabases($params)));
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
    private function listDatabases(array $params)
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
                        return false;
                    default:
                        return true;
                }
            }
        );
    }
}
