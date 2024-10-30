<?php
/**
 * Fired during plugin deactivation
 *
 * @link       http://www.daffodilsw.com/
 * @since      1.0.0
 *
 * @package    Convurt
 * @subpackage Convurt/includes
 */

class Wp_Convurt_Deactivator
{
    /**
     * 
     * @since    1.0.0
     */
    public static function deactivate()
    {
       // Remove the rewrite rule on deactivation
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
        // find out when the last event was scheduled
        $timestamp = wp_next_scheduled('wpconvurtjob');
        // unschedule previous event if any
        wp_unschedule_event($timestamp, 'wpconvurtjob');
            
    }
}
