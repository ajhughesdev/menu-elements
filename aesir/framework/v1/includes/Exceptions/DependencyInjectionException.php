<?php
/**
 * Created by PhpStorm.
 * User: finle
 * Date: 5/10/2016
 * Time: 7:18 PM
 */

namespace Aesir\v1\Exceptions;


class DependencyInjectionException extends AesirException {

    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct("Error initializing dependency " . $message, $code, $previous);
    }
}