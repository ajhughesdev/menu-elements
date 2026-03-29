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

    /** @var \DOMDocument */
    protected $dom;

    public function __construct(MenuElementRegistry $registry)
    {
        $this->registry = $registry;
        $this->dom = new \DOMDocument();
    }

    public function decorate($itemOutput, $item)
    {
        if (!$this->registry->hasDefinition($item->type)) {
            return $itemOutput;
        }

        $this->dom->loadHTML($itemOutput, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $menuItem = $this->dom->getElementById('menu-item-'.$item->ID);

        if (!$menuItem) {
            return $itemOutput;
        }

        $extra = [
            'menu-item-type--'.$item->type,
            'kmdg-custom-menu-item',
        ];

        $classes = $menuItem->getAttribute('class');
        $menuItem->setAttribute('class', $classes . ' ' . implode(' ', $extra));

        $finder = new \DOMXPath($this->dom);
        $nodes = $finder->query("//span[@class='menu-item-title']");

        if ($nodes->length > 0) {
            $titleElement = $nodes->item(0);
            $titleElement->nodeValue = $this->registry->getTypeLabel($item->type);
        }

        return str_replace('%09', '', $this->dom->saveHTML());
    }
}
