<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 10/8/2018
 * Time: 10:58 AM
 */

namespace Aesir\v1\Components\Template;


use Aesir\v1\Interfaces\IAesirTemplate;

class Template implements IAesirTemplate
{
    protected $location;
    protected $path;

    public function __construct($location, $path)
    {
        $this->location = $location;
        $this->path = $path;
    }

    public function getLocation() {
        return $this->location;
    }

    public function getPath() {
        return $this->path;
    }
}