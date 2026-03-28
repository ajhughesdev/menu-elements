<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 5/27/2019
 * Time: 11:17 PM
 */

namespace Aesir\v1\Traits;


trait TrimmedExplode
{
    /**
     * Performs a function similar to php explode(), but trims out whitespace around the individual
     * array elements similar to php trim(). This function uses regex amd so the character used
     * will be escaped if it has meaning in regex.
     *
     * @param string $input
     * @param string $char
     *
     * @return array[]|false|string[]
     */
    protected function explodeAndTrim($input, $char = ',') {
        $char = preg_quote($char, '/');

        $split = preg_split('/(\s*'.$char.'*\s*)*'.$char.'+(\s*'.$char.'*\s*)*/', trim($input));

        if(empty($split[0])) {
            unset($split[0]);
        }

        return $split;
    }
}