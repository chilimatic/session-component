<?php
declare(strict_types=1);

namespace chilimatic\lib\Session\Handler;

use chilimatic\lib\Session\Engine\Adapter\GenericEngine;
use chilimatic\lib\Session\Exception\SessionInvalidArgumentException;

class Handler
{

    /**
     * @var GenericEngine|null
     */
    private $engine;

    /**
     * @var array
     */
    private $sessionData = [];

    /**
     * @var array
     */
    private $sessionNameSpaceData = [];

    /**
     * @param GenericEngine|null $engine
     */
    public function __construct(GenericEngine $engine = null)
    {
        $this->engine = $engine;

        $this->getSessionData();
    }


    /**
     *
     */
    public function getSessionData()
    {
        foreach ($this->engine->sessionData as $key => $entry) {
            if (!($entry instanceof SessionNamespaceData)) {
                $this->sessionData[$key] = $entry;
            } else {
                $this->sessionNameSpaceData[$key] = $entry;
            }
        }
        $_SESSION = [];
    }


    /**
     * combines the the arrays and saves them to the session accordingly
     * -> this just sets the data -> the engine will decide when and where it will be saved (latest on the destructor call!)
     * i tried instant saving but I would have to hack the normal session behaviour so the second empty rewrite does not happen !
     */
    public function save()
    {
        $this->engine->setSessionData(array_merge($this->sessionData, $this->sessionNameSpaceData));
    }

    /**
     * @param $key
     *
     * @return null
     */
    public function get($key)
    {
        if (!$key || !isset($this->sessionData[$key])) {
            return null;
        }

        return $this->sessionData[$key];
    }

    /**
     * @param int|string $key
     * @param mixed $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function set($key, $value)
    {
        if (!$key) {
            throw new SessionInvalidArgumentException('Missing Key in Session Set Method');
        }
        $this->sessionData[$key] = $value;

        return $this;
    }

    /**
     * removes the key
     *
     * @param string $key
     *
     * @return $this
     */
    public function delete($key)
    {
        unset($this->sessionData[$key]);

        return $this;
    }

    /**
     * @param $namespace
     * @param $key
     * @param $value
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setByNamespace($namespace, $key, $value)
    {
        if (!$key || !$namespace) {
            throw new SessionInvalidArgumentException('Missing Key in Session Set Method');
        }

        if (!$this->sessionNameSpaceData[$namespace]) {
            $this->sessionNameSpaceData[$namespace] = ((new SessionNamespaceData($namespace))->set($key, $value));
        } else {
            $this->sessionNameSpaceData[$namespace]->set($key, $value);
        }

        return $this;
    }

}