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
    public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $itemOutput = '';
        parent::start_el($itemOutput, $item, $depth, $args, $id);

        $output .= MenuElements()->decorateEditWalkerItemOutput($itemOutput, $item);
    }

    public function end_el(&$output, $item, $depth = 0, $args = array())
    {
        $itemOutput = '';
        parent::end_el($itemOutput, $item, $depth, $args);

        $output .= $itemOutput;
    }
}
