<?php

namespace Aesir\v1\Exceptions;


class MissingDependency extends AesirException
{
    public function __construct($message = "", $code = 0, \Exception $previous = null)
    {
        parent::__construct(trim('Missing Dependency: '.$message), $code, $previous);
    }
}