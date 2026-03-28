<?php
/**
 * Created by PhpStorm.
 * User: finle
 * Date: 3/20/2016
 * Time: 2:25 AM
 */

namespace Aesir\v1\Components;

use Aesir\v1\Traits\Filterable;

class Cron
{
    use Filterable;

    const SLUG_PREFIX = 'aesir_cron_';

    /** @var string */
    protected $slug;

    /** @var string|int */
    protected $schedule;

    /** @var callable */
    protected $callback;

    /** @var Dependency */
    protected $dependency;

    public function __construct(Dependency $DI = null)
    {
        if(is_null($DI)) {
            $DI = Core::get_instance()->dependency();
        }

        $this->dependency = $DI;
    }

    public function make($slug, $schedule, callable $callback) {
        $this->slug = static::SLUG_PREFIX.$slug;
        $this->schedule = $schedule;
        $this->callback = $callback;

        add_action('wp', [$this, 'register_job']);
        add_action($this->slug, [$this, 'run_job']);
    }

    protected function __register_job() {
        if(!wp_next_scheduled($this->slug) && !defined('DOING_CRON')) {
            wp_schedule_single_event($this->get_next_time(), $this->slug);
        }
    }

    protected function __run_job() {
        $this->dependency->call($this->callback);
    }

    protected function get_next_time() {
        $schedules = wp_get_schedules();

        if(is_callable($this->schedule)) {
            return call_user_func($this->schedule);
        } elseif(!empty($schedules[$this->schedule])) {
            return time() + $schedules[$this->schedule]['interval'];
        } elseif(is_int($this->schedule)) {
            return time() + $this->schedule;
        }

        throw new \Exception('Invalid Cron Schedule');
    }

    /**
     * Allows for immediate deletion of an existing job
     */
    public static function delete($slug) {
        $slug = static::SLUG_PREFIX.$slug;
        wp_unschedule_event(wp_next_scheduled($slug), $slug);
    }
}