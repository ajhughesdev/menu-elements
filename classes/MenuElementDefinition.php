<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/29/2026
 * Time: 12:00 AM
 */

namespace KMDG\MenuElements;


class MenuElementDefinition
{
    protected $title = "";

    protected $slug = "";

    protected $prototypeItemData = [];

    protected $before;

    protected $main;

    protected $after;

    public function __construct($title, $slug, array $prototypeItemData, callable $main, $after = null, $before = null)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->prototypeItemData = $prototypeItemData;
        $this->main = $main;
        $this->after = $after;
        $this->before = $before;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getPrototypeMenuItem()
    {
        return (object) $this->prototypeItemData;
    }

    public function getItemContent($default, $item, $depth, $args)
    {
        if (is_callable($this->main)) {
            return call_user_func($this->main, $item, $depth, $args);
        }

        return $default;
    }

    public function getItemContentAfter($default, $item, $depth, $args)
    {
        if (is_callable($this->after)) {
            return call_user_func($this->after, $item, $depth, $args);
        }

        return $default;
    }

    public function getItemPreRender($item)
    {
        if (is_callable($this->before)) {
            return call_user_func($this->before, $item);
        }

        return $item;
    }
}
