<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 10/8/2018
 * Time: 11:10 AM
 */

namespace Aesir\v1\Interfaces;


interface IAesirTemplate
{
    /**
     * Returns the name of the location the template was found in (for debugging use), typically "parent" or "child" for
     * themes. Plugins can set their own values.
     *
     * @return string
     */
    public function getLocation();

    /**
     * Returns the full path to the template file.
     *
     * @return string
     */
    public function getPath();
}