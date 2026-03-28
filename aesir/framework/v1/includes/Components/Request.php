<?php

namespace Aesir\v1\Components;


use Aesir\v1\Components\Template\Template;
use Aesir\v1\Exceptions\RequestException;
use Aesir\v1\Traits\Filterable;
use Aesir\v1\Traits\Singleton;
use Aesir\v1\Interfaces\IAesirRequest;

class Request implements IAesirRequest
{
	use Singleton;
	use Filterable;

	protected $routeMethod;
	protected $routeType;
	protected $routeSlug;
	protected $routeMode;
	protected $id;
	protected $wpTemplate;
	protected $isLoaded;
	protected $query;
	protected $data = [];

	protected function initialize()
	{
		$this->routeMethod = strtoupper($_SERVER['REQUEST_METHOD']);
		$this->routeType = null;
		$this->routeMode = null;
		$this->id = null;
		$this->wpTemplate = null;
		$this->isLoaded = false;
		$this->query = [
			'GET'   => $_GET,
			'POST'  => $_POST
		];

		if(defined('DOING_AJAX') && DOING_AJAX) {
			$this->routeType = 'ajax';
			$this->routeMode = $this->get('action') ?? $this->post('action');
			$this->isLoaded = true;
		} else {
			add_filter('template_include', [$this, 'load'], 1);
		}
	}

	/**
	 * Loads data into the object at a point where wordpress is ready to provide it.
	 *
	 * @param $template
	 *
	 * @return string
	 */
	protected function __load($template) {

		$type = '';
		$mode = '';
		$slug = null;
		$id = null;

		if(is_author()) {
			$type = 'author';
			$mode = 'archive';
			$slug = get_the_author_meta('nickname');
			$id = get_the_author_meta('ID');
		} elseif(is_tax()) {
			$type = 'tax';
			$mode = get_query_var( 'taxonomy' );
			$slug = get_queried_object()->slug;
			$id = get_queried_object_id();
		} elseif(get_the_ID() !== false && get_option("page_for_posts") == get_the_ID()) {
			$type = 'post';
			$mode = 'archive';
			$slug = 'index';
			$id = (get_query_var('paged')) ? get_query_var('paged') : 1;
		} elseif(get_the_ID() !== false && is_page()) {
			$type = 'page';
			$mode = $this->templateSlug();
			$slug = get_post_field('post_name');
			$id = get_the_ID();
		} else {
			$type = get_query_var('post_type') ?: get_post_type();

			if(!is_404()) {
				if(is_archive() || get_the_ID() === false) {
					$mode = 'archive';
					$slug = 'index';
					$id = (get_query_var('paged')) ? get_query_var('paged') : 1;
				} else {
					$mode = 'single';
					$slug = $this->templateSlug();
					$id = get_the_ID();
				}
			} else {
				$mode = "error";
				$slug = "404";
				$id = $type;
			}
		}

		$this->routeType = $type;
		$this->routeMode = $mode;
		$this->routeSlug = $slug;
		$this->id = $id;
		$this->wpTemplate = $template;
		$this->isLoaded = true;

		return $template;
	}

	public function template() {
		if($this->isLoaded) {
			return new Template('wordpress', $this->wpTemplate);
		} else {
			throw new RequestException();
		}
	}

	public function type() {
		if($this->isLoaded) {
			return $this->routeType;
		} else {
			throw new RequestException();
		}
	}

	public function mode() {
		if($this->isLoaded) {
			return $this->routeMode;
		} else {
			throw new RequestException();
		}
	}

	public function method() {
		if($this->isLoaded) {
			return $this->routeMethod;
		} else {
			throw new RequestException();
		}
	}

	public function slug() {
		if($this->isLoaded) {
			return $this->routeSlug;
		} else {
			throw new RequestException();
		}
	}

	public function ID() {
		if($this->isLoaded) {
			return $this->id;
		} else {
			throw new RequestException();
		}
	}

	public function setData($key, $value) {
	    $this->data[$key] = $value;
    }

    public function getData($key) {
	    return $this->data[$key] ?? null;
    }

	public function get($key) {
		return isset($this->query['GET'][$key]) ? $this->query['GET'][$key] : null;
	}

	public function post($key) {
		return isset($this->query['POST'][$key]) ? $this->query['POST'][$key] : null;
	}

	public function __toString()
	{
		return $this->method().'/'.$this->type().'/'.$this->mode().'/'.$this->slug();
	}

	protected function templateSlug() {
	    $slug = get_page_template_slug();
	    return $slug == '' ? 'default' : sanitize_title(str_replace('.php', '', $slug));
    }
}