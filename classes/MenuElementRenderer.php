<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/29/2026
 * Time: 1:15 AM
 */

namespace KMDG\MenuElements;


class MenuElementRenderer
{
    /** @var MenuElementRegistry */
    protected $registry;

    /** @var MenuElementFieldResolver */
    protected $fieldResolver;

    public function __construct(MenuElementRegistry $registry, MenuElementFieldResolver $fieldResolver)
    {
        $this->registry = $registry;
        $this->fieldResolver = $fieldResolver;
    }

    public function getItemContent($default, $item, $depth, $args)
    {
        if ($this->registry->hasDefinition($item->type)) {
            return $this->registry->getDefinition($item->type)->getItemContent($default, $item, $depth, $args);
        }

        return $default;
    }

    public function getItemContentAfter($default, $item, $depth, $args)
    {
        if ($this->registry->hasDefinition($item->type)) {
            return $this->registry->getDefinition($item->type)->getItemContentAfter($default, $item, $depth, $args);
        }

        return $default;
    }

    public function getItemPreRender($item)
    {
        if ($this->registry->hasDefinition($item->type)) {
            return $this->registry->getDefinition($item->type)->getItemPreRender($item);
        }

        return $item;
    }

    public function rowCallback($item, $depth, $args)
    {
        return "";
    }

    public function columnCallback($item, $depth, $args)
    {
        $lineClass = $this->fieldResolver->isLineEnabled($item) ? 'menu-elements__column-wrap--line' : '';

        return "<div class='menu-elements__column-wrap {$lineClass}'>";
    }

    public function columnCallbackAfter($item, $depth, $args)
    {
        return "</div>";
    }

    public function columnCallbackBefore($item)
    {
        $item->classes[] = 'menu-elements__column--'.$this->fieldResolver->getColumnSize($item);

        return $item;
    }

    public function spacerCallback($item, $depth, $args)
    {
        $line = $this->fieldResolver->isLineEnabled($item);
        $lineClass = $line ? 'menu-elements__spacer--has-line' : '';
        $size = $this->fieldResolver->getSpacerSize($item) / 2;
        $border = $line ? 'border: 1px solid;' : '';

        return apply_filters('KMDG/MenuElements/Spacer/html',
            "<div style='overflow: hidden;'><div class='menu-elements__spacer {$lineClass}' style='padding-top: {$size}em;margin-bottom: {$size}em;{$border}'></div></div>",
            $item, $depth, $args, $line, $lineClass, $size, $border
        );
    }

    public function titleCallback($item, $depth, $args)
    {
        $title = get_the_title($item);

        return apply_filters('KMDG/MenuElements/Title/html', "<div class='menu-elements__title'>{$title}</div>", $item, $depth, $args, $title);
    }
}
