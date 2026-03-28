<?php
/**
 * Created by PhpStorm.
 * User: finle
 * Date: 5/9/2016
 * Time: 3:23 PM
 */

namespace Aesir\v1\Components;

use Aesir\v1\Components\Template\Template;
use Aesir\v1\Interfaces\IAesirTemplate;
use Aesir\v1\Interfaces\IAesirView;
use Aesir\v1\Traits\ArrayMerge;

class View implements IAesirView {

    use ArrayMerge;

    /** @var IAesirTemplate */
    protected $template;

    /** @var array */
    protected $templateData = [];

    /**
     * Construct a view by passing in its template file, this can be the original WordPress
     * template, or a file of your choice.
     *
     * @param IAesirTemplate|null $template
     */
    public function __construct(IAesirTemplate $template = null)
    {
        if (is_null($template)) {
            $template = new Template('none', null);
        }

        $this->template = $template;
    }

    /**
     * Sets the template to be used. If $template is null the value is not updated.
     *
     * @param IAesirTemplate|null $template
     *
     * @return View
     */
    public function template(IAesirTemplate $template = null) {
        if(!is_null($template)) {
            $this->template = $template;
        }

        return $this;
    }

    /**
     * Sets up variables for use in the template.
     *
     * @param array $templateData
     * @return View
     */
    public function make(array $templateData = [])
    {
        $this->templateData = $templateData;

        return $this;
    }

    /**
     * Extends any template variables added with make(), variables are added and replaced, but not removed.
     *
     * @param array $templateData
     * @return View
     */
    public function extend(array $templateData) {
        $this->templateData = $this->merge($this->templateData, $templateData);

        return $this;
    }

    /**
     * @see View::render()
     */
    public function __invoke()
    {
        $this->render();
    }

    /**
     * Renders the template using any variables passed into make(). The templateData array
     * is extracted and duplicate names prefixed with "data_". In addition to the template data,
     * various WordPress globals are provided as would normally be the case when loading a
     * WordPress template.
     *
     * This method also accepts on-the-fly variables passed into it, these are treated like
     * the templateData array but are not stored, so if the view is re-rendered the content of
     * those variables will be lost. Render-time data is given precident over data stored in
     * templateData to allow for override situations.
     *
     * @param array $renderData
     */
    protected function render(array $renderData = []) {
        global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;

        $templateData = $this->templateData;

        if ( is_array( $wp_query->query_vars ) )
            extract( $wp_query->query_vars, EXTR_SKIP );

        extract($renderData, EXTR_PREFIX_SAME|EXTR_REFS, 'data');
        extract($templateData, EXTR_PREFIX_SAME|EXTR_REFS, 'data');

        $template_name = $this->template->getLocation()."/".str_ireplace('.php', '', basename($this->template->getPath()));

        if(WP_DEBUG) echo "<!-- [START $template_name] -->";

        if (!is_null($this->template->getPath())) {
            include($this->template->getPath());
        } elseif(WP_DEBUG) {
            echo "<!-- [Template Not Found] -->";
        }

        if(WP_DEBUG) echo "<!-- [END $template_name] -->";
    }
}