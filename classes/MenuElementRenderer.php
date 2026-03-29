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

    private function getDefinitionOrNull($item)
    {
        if (!$this->registry->hasDefinition($item->type)) {
            return null;
        }

        return $this->registry->getDefinition($item->type);
    }

    public function getItemContent($default, $item, $depth, $args)
    {
        $definition = $this->getDefinitionOrNull($item);
        if ($definition === null) {
            return $default;
        }

        return $definition->getItemContent($default, $item, $depth, $args);
    }

    public function getItemContentAfter($default, $item, $depth, $args)
    {
        $definition = $this->getDefinitionOrNull($item);
        if ($definition === null) {
            return $default;
        }

        return $definition->getItemContentAfter($default, $item, $depth, $args);
    }

    public function getItemPreRender($item)
    {
        $definition = $this->getDefinitionOrNull($item);
        if ($definition === null) {
            return $item;
        }

        return $definition->getItemPreRender($item);
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
        $column_size = $this->fieldResolver->getColumnSize($item);

        if (!empty($column_size)) {
            $sanitized_column_size = sanitize_html_class($column_size);

            if ($sanitized_column_size !== '') {
                $item->classes[] = 'menu-elements__column--' . $sanitized_column_size;
            }
        }

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
        $escaped_title = esc_html($title);

        return apply_filters('KMDG/MenuElements/Title/html', "<div class='menu-elements__title'>{$escaped_title}</div>", $item, $depth, $args, $title);
    }
}
