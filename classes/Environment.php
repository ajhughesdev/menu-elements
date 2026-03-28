<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/28/2026
 * Time: 12:00 PM
 */

namespace KMDG\MenuElements;


class Environment
{
    protected $path = "";

    protected $url = "";

    public function __construct()
    {
        $this->path = plugin_dir_path(dirname(__FILE__));
        $this->url = plugin_dir_url(dirname(__FILE__));
    }

    public function getAcfJsonPath()
    {
        return $this->path . 'acf';
    }

    public function enqueueStyle($handle, $relativePath, $deps = [], $media = 'all')
    {
        wp_enqueue_style(
            $handle,
            $this->getAssetUrl($relativePath),
            $deps,
            $this->getAssetVersion($relativePath),
            $media
        );
    }

    protected function getAssetPath($relativePath)
    {
        return $this->path . ltrim($relativePath, '/');
    }

    protected function getAssetUrl($relativePath)
    {
        return $this->url . ltrim($relativePath, '/');
    }

    protected function getAssetVersion($relativePath)
    {
        return filemtime($this->getAssetPath($relativePath));
    }
}
