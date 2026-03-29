<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/29/2026
 * Time: 2:15 AM
 */

namespace KMDG\MenuElements;


class MenuElementEditorDecorator
{
    /** @var MenuElementRegistry */
    protected $registry;

    public function __construct(MenuElementRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function decorate($itemOutput, $item)
    {
        if (!$this->registry->hasDefinition($item->type)) {
            return $itemOutput;
        }

        $dom = new \DOMDocument();
        $dom->loadHTML($itemOutput, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $menuItem = $dom->getElementById('menu-item-'.$item->ID);

        if (!$menuItem) {
            return $itemOutput;
        }

        $extra = [
            'menu-item-type--'.$item->type,
            'kmdg-custom-menu-item',
        ];

        $classes = $menuItem->getAttribute('class');
        $menuItem->setAttribute('class', $classes . ' ' . implode(' ', $extra));

        $finder = new \DOMXPath($dom);
        $nodes = $finder->query("//span[@class='menu-item-title']");

        if ($nodes->length > 0) {
            $titleElement = $nodes->item(0);
            $titleElement->nodeValue = $this->registry->getTypeLabel($item->type);
        }

        return str_replace('%09', '', $dom->saveHTML());
    }
}
