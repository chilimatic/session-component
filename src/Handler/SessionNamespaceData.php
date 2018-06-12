<?php
declare(strict_types=1);

namespace chilimatic\lib\Session\Handler;


class SessionNamespaceData
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var array
     */
    private $sessionData;

    /**
     * @param string $namespace
     */
    public function __construct($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param mixed $namespace
     *
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSessionData()
    {
        return $this->sessionData;
    }

    /**
     * @param mixed $sessionData
     *
     * @return $this
     */
    public function setSessionData($sessionData)
    {
        $this->sessionData = $sessionData;

        return $this;
    }

    /**
     * @param $key
     * @param $value
     *
     * @return $this
     */
    public function set($key, $value)
    {
        if (!$key) {
            return $this;
        }

        $this->sessionData[$key] = $value;

        return $this;
    }

    /**
     * @param $key
     *
     * @return null
     */
    public function get($key)
    {
        if ($key === null || $key === '') {
            return null;
        }

        return $this->sessionData[$key];
    }
}