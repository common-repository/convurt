<?php
/**
 * Plugin Name: ConvurtGPT
 * Plugin URI: http://wordpress.org/plugins/convurt/
 * Description: Use ConvurtGPT to help write your blog posts. All you do is start writing your post, and when you need help, type /convurt and ConvurtGPT will write the next 500 characters for you.  Use it any time you need help writing.
 * Version: 2.0.2
 * Author: Joel Black
 * Author URI: https://www.linkedin.com/in/joellumonblack/
 * Text Domain: convurt
 */

function convurt_block_enqueue_assets() {
    wp_enqueue_style(
        'convurt-block-editor',
        plugins_url('editor.css', __FILE__),
        array('wp-edit-blocks'),
        filemtime(plugin_dir_path(__FILE__) . 'editor.css')
    );

    wp_enqueue_style(
        'convurt-block-style',
        plugins_url('style.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'style.css')
    );

    wp_enqueue_script(
        'convurt-block-ajax',
        plugin_dir_url(__FILE__) . 'ajax.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'jquery'),
        filemtime(plugin_dir_path(__FILE__) . 'ajax.js')
    );

    wp_localize_script('convurt-block-ajax', 'convurtBlock', array(
        'apiKey' => get_option('convurt_api_key'),
    ));
}

add_action('init', 'convurt_block_enqueue_assets');

function convurt_add_settings_page() {
    add_options_page(
        'Convurt API Key Settings', // Page title
        'Convurt API Key', // Menu title
        'manage_options', // Capability
        'convurt-api-key-settings', // Menu slug
        'convurt_render_settings_page' // Callback function
    );
}
add_action('admin_menu', 'convurt_add_settings_page');

function convurt_render_settings_page() {
    ?>
    <div class="wrap">
    <h1>ConvurtGPT API Settings</h1>
    <form method="post" action="options.php">
        <?php
        settings_fields('convurt_api_key_settings');
        do_settings_sections('convurt_api_key_settings');
        submit_button();
        ?>
    </form>
    </div>
    <?php
}

function convurt_register_api_key_setting() {
    register_setting(
        'convurt_api_key_settings', // Settings group
        'convurt_api_key' // Option name
    );

    add_settings_section(
        'convurt_api_key_section', // Section ID
        '', // Section title
        'convurt_api_key_section_callback', // Callback function
        'convurt_api_key_settings' // Page
    );

    add_settings_field(
        'convurt_api_key_field', // Field ID
        'OpenAI API KEY', // Field title
        'convurt_render_api_key_field', // Callback function
        'convurt_api_key_settings', // Page
        'convurt_api_key_section' // Section ID
    );
}
add_action('admin_init', 'convurt_register_api_key_setting');

function convurt_api_key_section_callback() {
    echo '<p>Please enter your OpenIA API key in the field below.  If you do not have one, you may register for one at <a target="_blank" href="https://platform.openai.com/"> platform.openai.com </a>. Then, look at the top right corner of the page, and click on the picture in your account.  Here you should be able to create an API key.</p>';
}

function convurt_render_api_key_field() {
    $api_key = get_option('convurt_api_key');
    echo '<input type="text" name="convurt_api_key" value="' . esc_attr($api_key) . '" size="50" />';
}


