<?php

error_reporting(E_ALL & ~E_DEPRECATED);

class MenuElementsTestFailure extends \RuntimeException
{
}

$GLOBALS['menu_elements_test_assertions'] = 0;

function menu_elements_test_increment_assertions()
{
    $GLOBALS['menu_elements_test_assertions']++;
}

function menu_elements_test_export($value)
{
    return var_export($value, true);
}

function menu_elements_test_fail($message)
{
    throw new MenuElementsTestFailure($message);
}

function menu_elements_test_assert_same($expected, $actual, $message = '')
{
    menu_elements_test_increment_assertions();

    if ($expected !== $actual) {
        $details = 'Expected ' . menu_elements_test_export($expected) . ' but got ' . menu_elements_test_export($actual) . '.';
        menu_elements_test_fail(trim($message . ' ' . $details));
    }
}

function menu_elements_test_assert_true($condition, $message = '')
{
    menu_elements_test_increment_assertions();

    if ($condition !== true) {
        menu_elements_test_fail($message !== '' ? $message : 'Expected condition to be true.');
    }
}

function menu_elements_test_assert_contains($needle, $haystack, $message = '')
{
    menu_elements_test_increment_assertions();

    if (strpos($haystack, $needle) === false) {
        $details = 'Did not find ' . menu_elements_test_export($needle) . ' in ' . menu_elements_test_export($haystack) . '.';
        menu_elements_test_fail(trim($message . ' ' . $details));
    }
}

function menu_elements_test_assert_instance_of($expectedClass, $value, $message = '')
{
    menu_elements_test_increment_assertions();

    if (!($value instanceof $expectedClass)) {
        $actualClass = is_object($value) ? get_class($value) : gettype($value);
        $details = 'Expected instance of ' . $expectedClass . ' but got ' . $actualClass . '.';
        menu_elements_test_fail(trim($message . ' ' . $details));
    }
}

function menu_elements_test_assert_throws(callable $callback, $expectedClass, $expectedMessagePart = '')
{
    menu_elements_test_increment_assertions();

    try {
        $callback();
    } catch (\Throwable $throwable) {
        if (!($throwable instanceof $expectedClass)) {
            menu_elements_test_fail('Expected exception ' . $expectedClass . ' but got ' . get_class($throwable) . '.');
        }

        if ($expectedMessagePart !== '' && strpos($throwable->getMessage(), $expectedMessagePart) === false) {
            menu_elements_test_fail('Expected exception message to contain ' . menu_elements_test_export($expectedMessagePart) . ' but got ' . menu_elements_test_export($throwable->getMessage()) . '.');
        }

        return;
    }

    menu_elements_test_fail('Expected exception ' . $expectedClass . ' to be thrown.');
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args)
    {
        return $value;
    }
}

if (!function_exists('get_field')) {
    function get_field($field, $item)
    {
        return isset($item->$field) ? $item->$field : null;
    }
}

if (!function_exists('get_the_title')) {
    function get_the_title($item)
    {
        return isset($item->title) ? $item->title : '';
    }
}

if (!function_exists('sanitize_html_class')) {
    function sanitize_html_class($class)
    {
        return preg_replace('/[^A-Za-z0-9_-]/', '', (string) $class);
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}
