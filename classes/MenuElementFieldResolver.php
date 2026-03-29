<?php
/**
 * Created by PhpStorm.
 * User: KMDG
 * Date: 3/29/2026
 * Time: 12:30 AM
 */

namespace KMDG\MenuElements;


class MenuElementFieldResolver
{
    public function isLineEnabled($item)
    {
        $value = $this->getFieldValue('enable_line', $item);

        if ($value === null || $value === '') {
            return false;
        }

        return !empty($value);
    }

    public function getColumnSize($item)
    {
        $value = $this->getFieldValue('column_size', $item);

        if ($value === null) {
            return '';
        }

        return (string) $value;
    }

    public function getSpacerSize($item)
    {
        $value = $this->getFieldValue('size', $item);

        if ($value === null || $value === '' || $value === false || !is_numeric($value)) {
            return 1;
        }

        return (float) $value;
    }

    protected function getFieldValue($field, $item)
    {
        if (!function_exists('get_field')) {
            return null;
        }

        return get_field($field, $item);
    }
}
