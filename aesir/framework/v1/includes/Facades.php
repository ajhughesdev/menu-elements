<?php
namespace Aesir\v1\Facades;

use Aesir\v1\Components\Utilities as AesirUtils;
use Aesir\v1\Aesir as AesirCore;
use Aesir\v1\Components\Router as AesirRouter;
use Aesir\v1\Components\test as AesirTemplate;

/**
 * @return AesirCore|$$class
 */
function Aesir($class = null) {
    if($class == null) {
        return AesirCore::getInstance();
    } else {
        return AesirCore::getInstance()->dependency()->get($class);
    }
}

/**
 * @return AesirUtils
 * @throws \DI\NotFoundException
 */
function Utilities() {
    return Aesir()->dependency()->get(AesirUtils::class);
}

/**
 * @return AesirTemplate
 * @throws \DI\NotFoundException
 */
function Template() {
    return Aesir()->dependency()->get(AesirTemplate::class);
}

/**
 * @return AesirRouter
 */
function Router() {
    return AesirRouter::getInstance();
}

