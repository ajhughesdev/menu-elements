<?php

namespace Aesir\v1\Interfaces;

interface IAesirView
{
    /**
     * Renders the template using any variables passed in via an associative array.
     *
     * @param array $templateData
     * @return $this
     */
    public function make(array $templateData);

    /**
     * Extends any template variables added with make(), variables are added and replaced, but not removed.
     *
     * @param array $templateData
     * @return $this
     */
    public function extend(array $templateData);

    /**
     * Renders the template using any variables passed into make().
     */
    public function __invoke();
}