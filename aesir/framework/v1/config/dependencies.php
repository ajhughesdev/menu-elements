<?php
/** @var Aesir\v1\Aesir $aesir */

use Aesir\v1\Aesir;
use Aesir\v1\Components\Request;
use Aesir\v1\Components\Router;
use Aesir\v1\Components\Template\Partial;
use Aesir\v1\Components\Template\TemplateLocator;
use Aesir\v1\Components\test;
use Aesir\v1\Components\View;
use Aesir\v1\Factories\PartFactory;
use Aesir\v1\Interfaces\IAesirRequest;
use Aesir\v1\Interfaces\IAesirTemplate;
use Aesir\v1\Exceptions\DependencyInjectionException;
use Aesir\v1\Interfaces\IAesirView;

use function DI\factory;
use function DI\get;

return [
    Aesir::class => factory(function() use ($aesir) {
        return $aesir;
    }),

    WP_Theme::class => factory(function() {
        return wp_get_theme();
    }),

    WP_Post::class => factory(function() {
        if($post = get_post()) {
            return $post;
        } else {
            throw new DependencyInjectionException("WP_Post, (Hint: WP_Post cannot be injected outside of The Loop)");
        }
    }),

    WP_User::class => factory(function() {
        return wp_get_current_user();
    }),

    Router::class => factory(function() use ($aesir) {
        return $aesir->router();
    }),

    IAesirRequest::class => factory(function() {
        return Request::getInstance();
    }),

    Request::class => get(IAesirRequest::class),

    IAesirView::class => factory(function () {
        return new View(Request::getInstance()->template());
    }),

    View::class => get(IAesirView::class),

    PartFactory::class => factory(function() {
        return new PartFactory(new TemplateLocator());
    }),
];