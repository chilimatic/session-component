<?php
declare(strict_types=1);

namespace chilimatic\lib\Session\Engine\Adapter;


use chilimatic\lib\Session\Exception\SessionInvalidArgumentException;
use PDO;

class PdoAdapter extends GenericEngine
{
    public const IDX_CONNECTION = 'pdo-connection';
    public const IDX_TABLE_NAME = 'table-name';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    private $tableName;

    /**
     * constructor
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        if (empty($config[self::IDX_CONNECTION])) {
            throw new SessionInvalidArgumentException('Session pdo connection is missing');
        }

        if (empty($config[self::IDX_TABLE_NAME])) {
            throw new SessionInvalidArgumentException('Session table name is missing');
        }

        parent::__construct($config);

        $this->setPdo($config[self::IDX_CONNECTION]);
        $this->setTableName($config[self::IDX_TABLE_NAME]);
    }

    /**
     * @param array $config
     * @return bool
     */
    public function init(array $config = []): bool
    {
        if (!($this->pdo instanceof \PDO)) {
            return false;
        }

        $sessionRes = $this->pdo->query("SELECT COUNT(`session_id`) FROM `{$this->tableName}`");

        if ($sessionRes === false) {
            $sql = "CREATE TABLE `{$this->_db_name}`.`user_session` (
                    `session_id` varchar(100) NOT NULL default '',
                    `session_data` LONGTEXT NOT NULL,
                    `expires` int(11) NOT NULL default '0',
                    `created` DATETIME,
                    `updated` DATETIME,
                     PRIMARY KEY (`session_id`)
                    ) ENGINE=InnoDB Collate=utf8_general_ci";

            return (bool) $this->pdo->exec($sql);
        }

        return true;
    }


    /**
     * @param $session_id string
     *
     * @return bool array
     */
    public function read($session_id)
    {
        // Set empty result
        $this->session_data = (string)'';

        // Fetch session data from the selected database with the correct
        // timestamp
        $time       = (int)time();
        $stmt = $this->pdo->prepare("SELECT `session_data` FROM `{$this->tableName}` WHERE `session_id` = :sessionId AND `expires` > :time");
        $stmt->bindValue(
            ':sessionId', $session_id
        );
        $stmt->bindValue(
            ':time', $time
        );

        // if no session data exists return false
        if (!$stmt->execute()) {
            return false;
        }

        $this->sessionData = $stmt->fetch(\PDO::FETCH_ASSOC)['session_data'];

        return $this->sessionData;
    }


    /**
     * write the session data to the database
     *
     * @param $session_id   string
     * @param $session_data string
     *
     * @return bool
     */
    public function write($session_id, $session_data)
    {
        // set the garbage collection timestamp
        $time = time() + (int)$this->getSessionLifeTime();

        $stmt = $this->pdo->prepare("
            REPLACE INTO `{$this->tableName}` (`session_id`,`session_data`,`expires`, `created`, `updated`) 
            VALUES (:sessionId, :sessionData, :time , NOW(), NOW())
        ");

        $stmt->bindValue(
            ':sessionId',
            $session_id
        );

        $stmt->bindValue(
            ':sessionData',
            $session_data
        );

        $stmt->bindValue(
            ':time',
            $time
        );

        if (!$stmt->execute()) {
            return false;
        }

        $this->sessionId = (string)$session_id;

        return true;
    }


    /**
     * garbage collector deletes the session
     *
     * @return bool
     */
    public function gc(): bool
    {
        return (bool) $this->pdo->exec("DELETE FROM `{$this->tableName}` WHERE `expires` < UNIX_TIMESTAMP();");
    }


    /**
     * destroy the session
     *
     * @param $session_id {string}
     *
     * @return bool
     */
    public function destroy($session_id)
    {

        if (empty($session_id)) {
            return false;
        }

        $stmt = $this->pdo->prepare("DELETE FROM `{$this->tableName}` WHERE `session_id` = :sessionId");
        $stmt->bindValue(':sessionId', $session_id);

        return (bool) $stmt->execute();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return true;
    }


    /**
     * destruct
     */
    public function __destruct()
    {
        session_write_close();
        // garbage collector should work instantly
        $this->gc();
    }

    /**
     * @return PDO|null
     */
    public function getPdo(): ?\PDO
    {
        return $this->pdo;
    }

    /**
     * @param PDO $pdo
     * @return PDO
     */
    private function setPdo(PDO $pdo): self
    {
        $this->pdo = $pdo;
        return $this;
    }

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return (string)$this->tableName;
    }

    /**
     * @param string $tableName
     * @return PdoAdapter
     */
    private function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }


}