<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 6/14/2019
 * Time: 5:17 PM
 */

namespace KMDG\MenuElements;


class EditWalker extends \Walker_Nav_Menu_Edit
{
    protected $dom;

    public function __construct()
    {
        $this->dom = new \DOMDocument();
    }

    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $itemOutput = '';
        parent::start_el($itemOutput, $item, $depth, $args, $id);

        $extra = ['menu-item-type--'.$item->type];

        if(in_array($item->type, MenuElements()->getTypes())) {
            $extra[] = 'kmdg-custom-menu-item';

            $this->dom->loadHTML($itemOutput, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $menuItem = $this->dom->getElementById('menu-item-'.$item->ID);
            $classes = $menuItem->getAttribute('class');
            $menuItem->setAttribute('class', $classes . ' ' . implode(' ', $extra));

            $finder = new \DOMXPath($this->dom);
            $nodes = $finder->query("//span[@class='menu-item-title']");

            if($nodes->length > 0) {
                $titleElement = $nodes->item(0);
                $titleElement->nodeValue = MenuElements()->getTypeLabel($item->type);
            }

            $output .= str_replace('%09', '', $this->dom->saveHTML());
        } else {
            $output .= $itemOutput;
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = array())
    {
        $itemOutput = '';
        parent::end_el($itemOutput, $item, $depth, $args);

        $output .= $itemOutput;
    }
}