<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Convurt
 * @subpackage Convurt/includes
 * @author     Convurt, LLC@https://convurt.io/        
 */
class Wp_Convurt_Activator
{
    /**
     *
     * @since    1.0.0
     */
    public static function activate()
    {
        // Remove the rewrite rule on activation
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
    }
}
