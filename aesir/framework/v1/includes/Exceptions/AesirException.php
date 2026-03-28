<?php
/**
 * Created by PhpStorm.
 * User: finle
 * Date: 5/10/2016
 * Time: 7:18 PM
 */

namespace Aesir\v1\Exceptions;


class AesirException extends \Exception {
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct(trim('[Aesir Framework] '.$message), $code, $previous);
    }
}