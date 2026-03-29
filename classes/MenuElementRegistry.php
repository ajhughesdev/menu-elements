<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/29/2026
 * Time: 12:00 AM
 */

namespace KMDG\MenuElements;


use Aesir\v1\Exceptions\AesirException;

class MenuElementRegistry
{
    protected $definitions = [];

    public function register(MenuElementDefinition $definition)
    {
        $this->definitions[$definition->getSlug()] = $definition;
    }

    public function registerLegacy($title, $slug, callable $main, $after = null, $before = null)
    {
        $this->register(
            new MenuElementDefinition(
                $title,
                $slug,
                $this->getPrototypeItemData($title, $slug),
                $main,
                $after,
                $before
            )
        );
    }

    public function getTypes()
    {
        return array_keys($this->definitions);
    }

    public function hasDefinition($slug)
    {
        return !empty($this->definitions[$slug]);
    }

    public function getDefinition($slug)
    {
        if (!$this->hasDefinition($slug)) {
            throw new AesirException("Cannot get definition for undefined type [$slug].");
        }

        return $this->definitions[$slug];
    }

    public function getTypeLabel($slug)
    {
        return $this->getDefinition($slug)->getTitle();
    }

    public function getMenuItems()
    {
        $menuItems = [];

        foreach ($this->definitions as $slug => $definition) {
            $menuItems[$slug] = $definition->getPrototypeMenuItem();
        }

        return $menuItems;
    }

    protected function getPrototypeItemData($title, $slug)
    {
        return [
            'ID' => 1,
            'db_id' => 0,
            'menu_item_parent' => 0,
            'object_id' => "column",
            'post_parent' => 0,
            'type' => $slug,
            'object' => $slug,
            'type_label' => $title,
            'title' => $title,
            'url' => null,
            'target' => '',
            'attr_title' => '',
            'description' => '',
            'classes' => ["menu-elements__$slug"],
            'xfn' => ''
        ];
    }
}
