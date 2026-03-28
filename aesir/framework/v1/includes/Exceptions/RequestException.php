<?php
/**
 * Created by PhpStorm.
 * User: finle
 * Date: 5/10/2016
 * Time: 7:18 PM
 */

namespace Aesir\v1\Exceptions;


class RequestException extends AesirException
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct(trim('Request object used before WordPress is ready. '.$message), $code, $previous);
    }
}