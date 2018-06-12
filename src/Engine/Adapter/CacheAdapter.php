<?php
declare(strict_types=1);

namespace chilimatic\lib\Session\Engine\Adapter;
use chilimatic\lib\Session\Exception\SessionInvalidArgumentException;

class CacheAdapter extends GenericEngine
{

    public const IDX_CACHE_ENGINE = 'cache-engine';

    /**
     * cache engine container
     *
     * @var ICache
     */
    private $engine;

    /**
     * init method to add tables or other needed behaviour
     *
     * @param array $config
     */
    public function init(array $config = []): void
    {
        if (!empty($config[self::IDX_CACHE_ENGINE])) {
            throw new SessionInvalidArgumentException(self::IDX_CACHE_ENGINE . ': is empty');
        }

        $this->setEngine($config[self::IDX_CACHE_ENGINE]);
    }

    /**
     * reads a specific session
     *
     * @param $sessionId
     *
     * @return mixed
     */
    public function read($sessionId)
    {
        // assign the current session id
        $this->sessionId   = (string)$sessionId;
        $this->sessionData = $this->engine->get($this->getPrefixedSessionId($sessionId));

        if (!$this->sessionData) {
            $this->sessionData = [];
        }

        // session data is set uncompressed
        return $this->sessionData;
    }

    /**
     * writes a specific session
     *
     * @param $sessionId
     * @param $sessionData
     *
     * @return mixed
     */
    public function write($sessionId, $sessionData)
    {
        $sessionData       = (!$sessionData || !\is_array($sessionData)) ? [] : $sessionData;
        $this->sessionData = (!$this->sessionData) ? [] : $this->sessionData;

        $this->sessionData = array_merge($sessionData, $this->sessionData);
        $this->engine->set(
            $this->getPrefixedSessionId($sessionId),
            $this->sessionData,
            $this->getSessionLifeTime()
        );

        return true;
    }

    /**
     * @param $sessionId
     * @return void
     */
    function destroy($sessionId)
    {
        $this->engine->delete(
            $this->getPrefixedSessionId($sessionId)
        );
    }

    public function close(): bool
    {
        return true;
    }

    /**
     * @return ICache
     */
    public function getEngine(): ICache
    {
        return $this->engine;
    }

    /**
     * @param ICache $engine
     * @return CacheAdapter
     */
    public function setEngine(ICache $engine): self
    {
        $this->engine = $engine;
        return $this;
    }
}