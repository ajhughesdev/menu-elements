<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 9/19/2017
 * Time: 1:59 PM
 */

namespace Aesir\v1\Interfaces;


interface IAesirRequest
{
    /**
     * The original template path that WordPress would have loaded.
     * @return string
     */
    public function template();

    /**
     * The request method, typically as returned by $_SERVER['REQUEST_METHOD']
     * @return string
     */
    public function method();

    /**
     * The requested post type
     * @return string
     */
    public function type();

    /**
     * The display mode for the request, such as single or archive. For pages this should
     * return the page template slug.
     * @return string
     */
    public function mode();

    /**
     * Returns a identifier slug for the requested page. Typically this will be the page/post slug but
     * can vary for pages with less clearly defined slugs.
     * @return string
     */
    public function slug();

    /**
     * @return int|boolean
     */
    public function ID();
}