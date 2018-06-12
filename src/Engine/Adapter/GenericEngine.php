<?php
declare(strict_types=1);

namespace chilimatic\lib\Session\Engine\Adapter;

use chilimatic\lib\Session\Engine\SessionEngineInterface;

abstract class GenericEngine implements SessionEngineInterface
{
    public const IDX_SESSION_LIFETIME = 'session_lifetime';
    public const IDX_SESSION_KEY = 'session_key';

    /**
     * specific session key if we use a cache storage
     *
     * @var string
     */
    private $sessionKey = 'sess.';

    /**
     * Session lifetime
     * defines the lifetime before a session gets invalid
     * default 1 hour
     *
     * @var int
     */
    private $sessionLifeTime;


    /**
     * session data
     *
     * @var array
     */
    public $sessionData = [];


    /**
     * session id
     *
     * @var string
     */
    protected $sessionId;

    /**
     * constructor to bind the session handler
     * to the current class
     *
     * @param [] $config
     */
    public function __construct(array $config = [])
    {
        $this->sessionLifeTime = get_cfg_var('session.gc_maxlifetime');
        $this->sessionLifeTime = $config[self::IDX_SESSION_LIFETIME] ?? $this->sessionLifeTime;
        $this->sessionKey      = $config[self::IDX_SESSION_KEY] ?? $this->sessionKey;

        $this->register();
    }

    protected function register(): void
    {
        session_set_save_handler([
            &$this,
            'open'
        ], [
            &$this,
            'close'
        ], [
            &$this,
            'read'
        ], [
            &$this,
            'write'
        ], [
            &$this,
            'destroy'
        ], [
            &$this,
            'gc'
        ]);
    }


    /**
     * @param string $sessionId
     *
     * @return string
     */
    protected function getPrefixedSessionId(string $sessionId): string
    {
        return $this->sessionKey . $sessionId;
    }

    /**
     * @param string $key
     * @param string $nameSpace
     * @return string
     */
    protected function getSessionKey(string $key, string $nameSpace = ''): string {
        return $nameSpace . $key;
    }

    /**
     * http://php.net/manual/en/session.configuration.php for options
     *
     * @param array $options
     */
    public function start(array $options = []): void
    {
        session_start($options);
    }

    /**
     * init method to add tables or other needed behaviour
     *
     * @param array $config
     *
     * @return mixed
     */
    abstract public function init(array $config = []);

    /**
     * reads a specific session
     *
     * @param string $sessionId
     *
     * @return mixed
     */
    abstract public function read($sessionId);

    /**
     * writes a specific session
     *
     * @param string $sessionId
     * @param mixed $sessionData
     *
     * @return mixed
     */
    abstract public function write($sessionId, $sessionData);

    /**
     * opens a specific session
     *
     * @param string $savePath
     * @param string $sessionName
     *
     * @return mixed
     */
    public function open($savePath, $sessionName)
    {
        // Don't need to do anything. Just return TRUE.
        return true;
    }

    /**
     * session garbage collector
     *
     * @return mixed
     */
    public function gc()
    {
        return true;
    }

    /**
     * destroys the session
     *
     * @param $sessionId
     *
     * @return mixed
     */
    abstract function destroy($sessionId);

    /**
     * close the session
     *
     * @return mixed
     */
    public function session_close()
    {
        // return true atm there is nothing specific needed
        return true;
    }

    /**
     * @return array
     */
    public function getSessionData()
    {
        return $this->sessionData;
    }

    /**
     * @param array $sessionData
     *
     * @return $this
     */
    public function setSessionData(array $sessionData)
    {
        $this->sessionData = $sessionData;
        $_SESSION          = $this->sessionData;

        return $this;
    }

    /**
     * @return int
     */
    public function getSessionLifeTime(): int
    {
        return (int)$this->sessionLifeTime;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return (string)$this->sessionId;
    }
}