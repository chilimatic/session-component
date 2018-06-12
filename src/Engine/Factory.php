<?php
declare(strict_types=1);

namespace chilimatic\lib\Session\Engine;

use chilimatic\lib\Session\Engine\Adapter\GenericEngine;
use chilimatic\lib\Session\Exception\SessionInvalidArgumentException;

class Factory
{
    /**
     * @param string $sessionEngineClassName
     * @param array $config
     *
     * @return GenericEngine|null
     * @throws SessionInvalidArgumentException
     */
    public static function make(string $sessionEngineClassName, array $config = []): ?GenericEngine
    {
        if (!class_exists($sessionEngineClassName, true)) {
            throw new SessionInvalidArgumentException('Session Engine ' . $sessionEngineClassName . ' does not exist');
        }

        return new $sessionEngineClassName($config);
    }
}