<?php
/**
 * The file that defines the core plugin class
 *
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Convurt
 * @subpackage Convurt/includes
 * @author     Convurt, LLC@https://convurt.io/        
 */
class Wp_Convurt
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Wp_Convurt_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.1.0
     */
    public function __construct()
    {

        $this->plugin_name = 'convurt';
        $this->version = '1.0.0';

        $this->wpconvurt_load_dependencies();
        $this->wpconvurt_define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Wp_Convurt_Loader. Orchestrates the hooks of the plugin.
     * - Wp_Convurt_Admin. Defines all hooks for the admin area.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function wpconvurt_load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-convurt-loader.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-convurt-admin.php';

        $this->loader = new Wp_Convurt_Loader();
    }

   
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function wpconvurt_define_admin_hooks()
    {

        $plugin_admin = new Wp_Convurt_Admin($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'wpconvurt_enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'wpconvurt_enqueue_scripts');

        // Can be used later on
        
        $this->loader->add_action('admin_menu', $plugin_admin, 'wpconvurt_plugin_settings');
        $this->loader->add_action('admin_notices', $plugin_admin, 'wpconvurt_update_notice');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function wpconvurt_run()
    {
        $this->loader->wpconvurt_run_hooks();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
