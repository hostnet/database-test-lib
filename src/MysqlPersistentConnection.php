<?php
/**
 * @copyright 2016-2017 Hostnet B.V.
 */
namespace Hostnet\Component\DatabaseTest;

/**
 * Start a mysql database daemon and create a temporary database.
 * Credentials can be retrieved by calling getConnectionParams.
 * The database will be removed once the object gets destructed.
 * The daemon will keep on running and will be reused for successive
 * connections. This part is handled by the bash script.
 *
 * When PHP exists or crashes, the bash script will notice and
 * remove the database(s).
 */
class MysqlPersistentConnection implements ConnectionInterface
{
    /**
     * Bash script taking care of daemon start,
     * database creation, destruction and providing
     * the credentials for the connection.
     *
     * If you close stdin of the process, the script
     * will assume you are done and remove your database.
     */
    const CMD_PERSISTENT = __DIR__ . '/../bin/mysql_persistent.sh';

    const CMD_TRAVIS = __DIR__ . '/../bin/mysql_travis.sh';

    const CMD_GITLAB = __DIR__ . '/../bin/mysql_gitlab.sh';

    /**
     * @var array
     */
    private $connection_params = [];

    /**
     * @var resource
     */
    private $pipe;

    /**
     * @var resource
     */
    private $process;

    /**
     * Start the daemon if needed and create a database.
     */
    public function __construct()
    {
        $descriptor_spec = [
            0 => ['pipe', 'r'],  // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'],  // stdout is a pipe that the child will write to
        ];

        $cmd = self::CMD_PERSISTENT;
        if (getenv('TRAVIS')) {
            $cmd = self::CMD_TRAVIS;
        } elseif (getenv('GITLAB_CI')) {
            $cmd = self::CMD_GITLAB;
        }

        $this->process = proc_open($cmd, $descriptor_spec, $pipes);
        $data          = fread($pipes[1], 1024);

        fclose($pipes[1]);
        $this->pipe = $pipes[0];

        foreach (explode(',', $data) as $param) {
            if (strpos($param, ':') !== false) {
                list($key, $value)                   = explode(':', $param);
                $this->connection_params[trim($key)] = trim($value);
            }
        }
    }

    /**
     * Cleanup the database
     */
    public function __destruct()
    {
        fclose($this->pipe);
        proc_close($this->process);
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getConnectionParams()
    {
        return $this->connection_params;
    }
}
