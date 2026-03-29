<?php

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/../aesir/framework/v1/includes/Exceptions/AesirException.php';
require __DIR__ . '/../classes/MenuElementDefinition.php';
require __DIR__ . '/../classes/MenuElementRegistry.php';
require __DIR__ . '/../classes/MenuElementFieldResolver.php';
require __DIR__ . '/../classes/MenuElementRenderer.php';

$tests = [];

$tests['registry exposes definitions and prototype menu items'] = function () {
    $registry = new \KMDG\MenuElements\MenuElementRegistry();

    $registry->registerLegacy('Column', 'column', function () {
        return '';
    });

    menu_elements_test_assert_same(['column'], $registry->getTypes());
    menu_elements_test_assert_same('Column', $registry->getTypeLabel('column'));
    menu_elements_test_assert_instance_of(\KMDG\MenuElements\MenuElementDefinition::class, $registry->getDefinition('column'));
    menu_elements_test_assert_same(null, $registry->getDefinitionOrNull('missing'));

    $menuItems = $registry->getMenuItems();
    menu_elements_test_assert_same('column', $menuItems['column']->type);
    menu_elements_test_assert_same(['menu-elements__column'], $menuItems['column']->classes);

    menu_elements_test_assert_throws(function () use ($registry) {
        $registry->getTypeLabel('missing');
    }, \Aesir\v1\Exceptions\AesirException::class, 'Cannot get label for undefined type [missing].');
};

$tests['field resolver applies stable defaults and normalized values'] = function () {
    $resolver = new \KMDG\MenuElements\MenuElementFieldResolver();

    $defaultItem = (object) [];
    menu_elements_test_assert_same(false, $resolver->isLineEnabled($defaultItem));
    menu_elements_test_assert_same('', $resolver->getColumnSize($defaultItem));
    menu_elements_test_assert_same(1, $resolver->getSpacerSize($defaultItem));

    $configuredItem = (object) [
        'enable_line' => '1',
        'column_size' => 'maximize',
        'size' => '2.5',
    ];

    menu_elements_test_assert_same(true, $resolver->isLineEnabled($configuredItem));
    menu_elements_test_assert_same('maximize', $resolver->getColumnSize($configuredItem));
    menu_elements_test_assert_same(2.5, $resolver->getSpacerSize($configuredItem));

    $emptyItem = (object) [
        'enable_line' => '',
        'size' => '',
    ];

    menu_elements_test_assert_same(false, $resolver->isLineEnabled($emptyItem));
    menu_elements_test_assert_same(1, $resolver->getSpacerSize($emptyItem));
};

$tests['renderer resolves pre-render and markup decisions through the seams'] = function () {
    $registry = new \KMDG\MenuElements\MenuElementRegistry();
    $fieldResolver = new \KMDG\MenuElements\MenuElementFieldResolver();
    $renderer = new \KMDG\MenuElements\MenuElementRenderer($registry, $fieldResolver);

    $registry->registerLegacy('Column', 'column', [$renderer, 'columnCallback'], [$renderer, 'columnCallbackAfter'], [$renderer, 'columnCallbackBefore']);
    $registry->registerLegacy('Spacer', 'spacer', [$renderer, 'spacerCallback']);
    $registry->registerLegacy('Title', 'title', [$renderer, 'titleCallback']);

    $column = (object) [
        'type' => 'column',
        'classes' => [],
        'enable_line' => '1',
        'column_size' => 'maximize',
    ];

    $column = $renderer->getItemPreRender($column);
    menu_elements_test_assert_same(['menu-elements__column--maximize'], $column->classes);
    menu_elements_test_assert_same("<div class='menu-elements__column-wrap menu-elements__column-wrap--line'>", $renderer->getItemContent('', $column, 0, (object) []));
    menu_elements_test_assert_same('</div>', $renderer->getItemContentAfter('', $column, 0, (object) []));

    $unsafeColumn = (object) [
        'type' => 'column',
        'classes' => [],
        'column_size' => 'ma x!mize',
    ];

    $unsafeColumn = $renderer->getItemPreRender($unsafeColumn);
    menu_elements_test_assert_same(['menu-elements__column--maxmize'], $unsafeColumn->classes);

    $spacer = (object) [
        'type' => 'spacer',
        'enable_line' => '',
        'size' => '2',
    ];
    $spacerMarkup = $renderer->getItemContent('', $spacer, 0, (object) []);
    menu_elements_test_assert_contains("class='menu-elements__spacer ", $spacerMarkup);
    menu_elements_test_assert_contains("padding-top: 1em;margin-bottom: 1em;", $spacerMarkup);

    $title = (object) [
        'type' => 'title',
        'title' => '<Section & Title>',
    ];
    $titleMarkup = $renderer->getItemContent('', $title, 0, (object) []);
    menu_elements_test_assert_contains("class='menu-elements__title'", $titleMarkup);
    menu_elements_test_assert_contains('&lt;Section &amp; Title&gt;', $titleMarkup);

    $unknown = (object) ['type' => 'missing'];
    menu_elements_test_assert_same('fallback', $renderer->getItemContent('fallback', $unknown, 0, (object) []));
    menu_elements_test_assert_same('after-fallback', $renderer->getItemContentAfter('after-fallback', $unknown, 0, (object) []));
    menu_elements_test_assert_same($unknown, $renderer->getItemPreRender($unknown));
};

$passed = 0;
$failed = 0;

foreach ($tests as $name => $test) {
    try {
        $test();
        echo "PASS {$name}\n";
        $passed++;
    } catch (\Throwable $throwable) {
        echo "FAIL {$name}\n";
        echo $throwable->getMessage() . "\n";
        $failed++;
    }
}

echo "\n";
echo "Assertions: " . $GLOBALS['menu_elements_test_assertions'] . "\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

exit($failed === 0 ? 0 : 1);
