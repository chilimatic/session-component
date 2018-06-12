<?php
declare(strict_types=1);

namespace chilimatic\lib\Session\Engine;

interface SessionEngineInterface
{
    /**
     * default sesion life time in seconds
     *
     * @var int
     */
    public const SESSION_LIFETIME = 3600;

    /**
     * constructor to bind the session handler
     *
     * @param array $config
     */
    public function __construct(array $config = []);

    /**
     * init method to add tables or other needed behaviour
     *
     * @param array $config
     *
     * @return mixed
     */
    public function init(array $config = []);

    /**
     * reads a specific session
     *
     * @param $session_id
     *
     * @return mixed
     */
    public function read($session_id);

    /**
     * writes a specific session
     *
     * @param $session_id
     * @param $session_data
     *
     * @return mixed
     */
    public function write($session_id, $session_data);

    /**
     * opens a specific session
     *
     * @param $save_path
     * @param $session_name
     *
     * @return mixed
     */
    public function open($save_path, $session_name);

    /**
     * session garbage collector
     *
     * @return mixed
     */
    public function gc();

    /**
     * destroys the session
     *
     * @param $session_id
     *
     * @return mixed
     */
    public function destroy($session_id);

    /**
     * close the session
     *
     * @return mixed
     */
    public function close();
}