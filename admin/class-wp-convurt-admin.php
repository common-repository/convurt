<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Convurt
 * @subpackage Convurt/admin
 * @author     Convurt, LLC@https://convurt.io/  
 */
class Wp_Convurt_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
        add_action('wp_ajax_wpconvurt_update_account_key', array($this,'wpconvurt_update_account_key_request'));
        add_action('wp_ajax_nopriv_wpconvurt_update_account_key', array($this,'wpconvurt_update_account_key_request'));
    }

    /**
     * Register the stylesheets for the admin area.
     * @since    1.0.0
     */
    public function wpconvurt_enqueue_styles()
    {
        wp_enqueue_style('bootstrap.min.css', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');    
        wp_enqueue_style('custom.css', plugin_dir_url(__FILE__) . 'css/custom.css', array(), $this->version, 'all');    
    }

    /**
     * Register the JavaScript for the admin area.
     * @since    1.0.0
     */
    public function wpconvurt_enqueue_scripts()
    {
        wp_enqueue_script('bootstrap.min.js', plugin_dir_url(__FILE__) .'js/bootstrap.min.js', array('jquery'), $this->version, false);
        wp_enqueue_script('custom.js', plugin_dir_url(__FILE__) .'js/custom.js', array('jquery'), $this->version, false);  
    }

    /*
     * Adds the plugin settings menu item to dashboard
     * @since    1.0.0
     */
    public function wpconvurt_plugin_settings()
    {
        $settings_page = add_menu_page('Convurt', 'Convurt', 'administrator', 'wpconvurt-settings', array($this, 'wpconvurt_settings_page'));
    }

    /*
     * Renders settings page
     * @since  1.0.0
     */
    public function wpconvurt_settings_page()
    { ?>
        <div class="wrap convurt">
            <div class="col-lg-12">
                <div class="heading">
                    <img src="<?php echo  plugins_url('admin/images/app_convurt.png',dirname(__FILE__)); ?>" width="50">
                    <h3><?php _e( 'Convurt Settings', 'convurt' ); ?></h3>
                </div>
            </div>
            
            <div class="alert alert-info">
                <p><?php _e( 'To connect your website with the convurt you need to enter convurt account id which you can get from the profile page after login to the ', 'convurt' ); ?><a href="https://app.convurt.io/"><?php _e( 'Convurt', 'convurt' ); ?></a><?php _e( ' website.', 'convurt' ); ?></p>

                <p><?php _e( 'After establish connection with the convurt you will get all the converted voice in your wordpress website. You can manually import them by click on synchronization button. Please note that on uninstall plugin all the imported posts will be removed. You will have to activate this plugin to use posts.', 'convurt' ); ?></p>
            </div>
        
        <?php 
        /** 
        * Conditions on submit account key from the form.
        **/
        if (isset($_POST["wpconvurt-settings-submit"]) && $_POST["wpconvurt-settings-submit"] == 'Y') {
            check_admin_referer("wpconvurt-settings-page");
            $this->wpconvurt_save_theme_settings();
        }
        if (isset($_POST["wpconvurt-settings-sync"]) && $_POST["wpconvurt-settings-sync"] == 'Y') {
            check_admin_referer("wpconvurt-settings-page");
            $this->wpconvurt_save_theme_settings_sync();
        }
        
        $convurt_api_user_option = get_option('convurt_api_user_option');

        if (false === $convurt_api_user_option) {
            echo $this->wpconvurt_settings_page_save_api();
        }

        if ($convurt_api_user_option != "") {
          echo $this->wpconvurt_settings_page_sync_data($convurt_api_user_option);
        } ?>
        </div>

        <?php $this->wpconvurt_settings_update_popup();
    }

    /*
     * Renders settings page Save Account Key html
     * @since  1.0.0
    */
    public function wpconvurt_settings_page_save_api()
    {
        global $pagenow; 
        $convurt_post_type_option = get_option('convurt_post_type_option');
        $selectedtext = 'selected="selected"';
        $html = '<div id="poststuff">
            <form method="post" action="'.admin_url('admin.php?page=wpconvurt-settings').'">
                '.wp_nonce_field("wpconvurt-settings-page").'
                <div class="row">
                    <div class="container col-lg-6">
                        <h4>'.__( 'Convurt Account Details:', 'convurt' ).'</h4><br>
                        
                        <label for="accountid">'. __( 'Account Key:', 'convurt' ).'</label>
                        
                        <div class="input-group">
                            '.wp_nonce_field( 'convurto_nonce_field', 'convurto_nonce_field_name' ).'
                            <input type="text" class="form-control" autocomplete="off" required id="accountid" name="accountkey">

                            <span class="input-group-addon">

                                <a class="my-tool-tip" data-toggle="tooltip" data-placement="top" title="'. __( 'Account ID can be get from the profile page after login to the https://app.convurt.io/ website.', 'convurt' ).'">
                                    <i class="glyphicon glyphicon-info-sign"></i>
                                </a>

                            </span>

                            </input>
                        </div>
                        <br>

                         <div class="form-group">
                            <label for="post_status">Select post type: </label>
                            <span class="">

                                <a class="my-tool-tip" data-toggle="tooltip" data-placement="top" title="'. __( 'By default all the imported posts will be saved as status draft. You can change post status to publish from this option.', 'convurt' ).'">
                                    <i class="glyphicon glyphicon-info-sign"></i>
                                </a>

                            </span>
                            
                            <select class="form-control selectpicker" id="post_type" name="post_type">
                                <option  value="draft">Draft</option>
                                <option value="publish">Publish</option>
                            </select></div><br>
                        <div class="form-group">
                            <input type="submit" name="submit" class="button-primary" value="'.__( 'Update Settings', 'convurt' ).'" />
                            <input type="hidden" name="wpconvurt-settings-submit" value="Y" />
                        </div>
                    </div> 
                </div>       
            </form>
        </div>';
        return $html;
    }

    /*
     * Saves the settings in tabs
     * @since    1.0.0
    */
    public function wpconvurt_save_theme_settings()
    {
        global $pagenow; 
        if ($pagenow == 'admin.php' && $_GET['page'] == 'wpconvurt-settings') {

            $err  = ''; 
            $arr  = ''; $post_type = sanitize_text_field($_POST['post_type']); 
            $convurt_post_type_option = get_option('convurt_post_type_option');
            
            if( $post_type != "") {
                if (false === $convurt_post_type_option) {
                    add_option('convurt_post_type_option', $post_type);
                } else {
                    update_option('convurt_post_type_option', $post_type);
                }
            }

            $returnedresult = $this->wpconvurt_ConvurtApiRequestResponse(sanitize_text_field($_POST['accountkey']));
            $result         = json_decode($returnedresult);
            
            if (!empty($result)) {
                $sync                    = 'on';
                $accountid               = isset($result->accountid) ? $result->accountid : '';
                $convurt_api_user_option = get_option('convurt_api_user_option');
                if($accountid != "") {
                    if (false === $convurt_api_user_option) {
                        add_option('convurt_api_user_option', $accountid);
                    } else {
                        update_option('convurt_api_user_option', $accountid);
                    }
                }
                if(isset($result->is_data) && $result->is_data == 1){
                    foreach ($result->data as $key => $value) {
                        if(isset($value->post_id) && $value->post_id != ""){
                            $convurt_post_type_option = get_option('convurt_post_type_option');
                            $my_post = array(
                                'post_title'    => wp_strip_all_tags($value->title),
                                'post_content'  => $value->voice_conversion_text,
                                'post_status'   => $convurt_post_type_option,
                                'post_author'   => get_current_user_id()
                            );
                            $post_id = wp_insert_post($my_post);
                            if ($post_id) {
                                add_post_meta($post_id, 'voice_duration', $value->voice_duration);
                                add_post_meta($post_id, 'voice_id', $value->post_id);
                                $poststatus = 'success';
                            }
                        }  
                    }
                }
            }
            if (!empty($result)) { 
                if ($result->msg != ""){
                    echo '<div class="' . $result->class . '"><p>' . $result->msg . '</p></div>';
                }
            }
        }
    }

    /*
     * Request Api, Validate and get data.
     * @since  1.0.0
     * @param  string    $accountid       The api account key.
    */
    public function wpconvurt_ConvurtApiRequestResponse($accountid = "")
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $errmsg   = "";
        $url      = 'https://app.convurt.io/ws/getConvurtUser';
        $data     = array(
            'accountid' => $accountid
        );
        $jsondata = json_encode($data);
        $response = wp_remote_post($url, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(),
            'body'        => $jsondata,
            'cookies'     => array()
        ));
       
       if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $status = json_decode($response['body'], true);
            if ($status['is_success'] == 0 && $status['is_valid_user'] == 0) {
                $returndata = array(
                    'flag'   => 0,
                    'msg'    => $status['message'],
                    'class'  => 'alert alert-danger'
                );
            }
            if ($status['is_success'] == 0 && $status['is_valid_user'] == 1){
                echo $post_type; 
                $returndata = array(
                    'flag'      => 1, 
                    'is_data'   => 0, 
                    'msg'       => $status['message'],
                    'class'     => 'alert alert-danger',
                    'accountid' => $status['accountid'],
                );
            }
            if ($status['is_success'] == 1) {
                echo $post_type; 
                $returndata = array(
                    'flag'      => 1, 
                    'is_data'   => 1, 
                    'msg'       => $status['message'],
                    'class'     => 'alert alert-success',
                    'accountid' => $status['data']['accountid'],
                    'data'      => $status['data'],
                );
            }
            return json_encode($returndata);
        }
    }
    
    /*
     * Renders settings page Save Sync data
     * @since  1.0.0
     * @param  string  $convurt_api_user_option  The api account key get from database.
    */
    public function wpconvurt_settings_page_sync_data($convurt_api_user_option)
    {
        global $pagenow; 

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        $args = array(
            'post_type'      => 'post',
            'order'          => 'ASC',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => 'voice_id',
                    'value'   => '',
                    'compare' => '!='
                    )
                )
        );

        $form = '<div class="wpconvurt_sync_form col-lg-12">';
        $form .= '<h5 class="alert alert-info">'.__( 'Converted voice will be automatically added in your website. You can manually import data by click on the synchronize button.', 'convurt' ).'</h5>';
        $form .= '<h4>'.__( 'Convurt Account Details:', 'convurt' ).'</h4><div class="clearfix"></div>';
        $form .= '<a class="wpconvurt_edit_account_key" data-toggle="modal" data-target="#myModalNorm"><button type="button" class="btn btn-info">Edit Settings <span class="glyphicon glyphicon-pencil"></span></button> </a>';
        $form .= '<div class="clearfix"></div><br/>';
        $form .= '<label for="accountid">'.__( 'Account Key:', 'convurt' ).'</label>';
        $form .= '<div class="input-group col-lg-4">';
        $form .= '<input type="text" class="form-control" value="'.$convurt_api_user_option.'" autocomplete="off" readonly required id="accountid" name="accountid">';
        $form .= '<span class="input-group-addon"><a class="my-tool-tip" data-toggle="tooltip" data-placement="top" title="'.__( 'Account ID can be get from the profile page after login to the https://app.convurt.io/ website.', 'convurt' ).'"><i class="glyphicon glyphicon-info-sign"></i></a></span></input>';
        $form .= '</div>';
        $form .= '<div class="clearfix"></div><br/>';
        $form .= '<form action="'.admin_url('admin.php?page=wpconvurt-settings').'" method="post">'.wp_nonce_field("wpconvurt-settings-page");
        $form .=  wp_nonce_field( 'synch_nonce_field', 'synch_nonce_field_name' );
        $form .= '<input type="hidden" name="accountkey" value="' . $convurt_api_user_option . '"><input type="hidden" name="wpconvurt-settings-sync" value="Y" />';
            
        $posts_exist = new WP_Query( $args );
        if($posts_exist->have_posts()):  
          while ($posts_exist->have_posts()) : $posts_exist->the_post();
            $form .= '<input type="hidden" name="post_id[]" value="' . get_post_meta(get_the_ID(), 'voice_id', true) . '">';
          endwhile;
        endif;  
        $form .= '<button type="submit" name="synchronize" class="btn btn-success">'.__( 'Synchronize Data ', 'convurt' ).'<i class="icon-user icon-white"></i></button><br/>';
        $form .= '<p><i>'.__( 'Click on the button to import converted voice text as a post in your website.', 'convurt' ).'</i></p>';
        $form .= '</form>';
        $form .= '</div>';
        return $form;
    }

    /*
     * Saves the settings Sync data
     * @since    1.0.0
     */
    public function wpconvurt_save_theme_settings_sync()
    {
        global $pagenow;
        if ($pagenow == 'admin.php' && $_GET['page'] == 'wpconvurt-settings') {
            if (isset($_POST['synchronize'])) {
                if(wp_verify_nonce($_POST['synch_nonce_field_name'],'synch_nonce_field')){

                    $poststatus   = ''; 
                    $arr          = '';
                    $account_id   = '';
                    $post_id      = '';
                    $post_id_arr  = '';

                    if(isset($_POST['accountkey']))      
                      $account_id_key = sanitize_text_field($_POST['accountkey']);
                    if(isset($_POST['post_id']))
                      $post_id_arr = filter_var_array($_POST['post_id'],FILTER_SANITIZE_STRING);

                    $Syncdata   = $this->wpconvurt_ConvurtApiPostData($account_id_key, $post_id_arr);
                    $result     = json_decode($Syncdata);
                    
                    if( !empty( $result->data ) ){
                        foreach ( $result->data as $key => $value ) {
                            if( $value->post_id != "" ){
                                $convurt_post_type_option = get_option('convurt_post_type_option');
                                $my_post = array(
                                    'post_title'    => wp_strip_all_tags($value->title),
                                    'post_content'  => $value->voice_conversion_text,
                                    'post_status'   => $convurt_post_type_option,
                                    'post_author'   => get_current_user_id()
                                );
                                $post_id = wp_insert_post($my_post);
                                if ($post_id){
                                    add_post_meta($post_id, 'voice_duration', $value->voice_duration);
                                    add_post_meta($post_id, 'voice_id', $value->post_id);
                                    $poststatus = 'success';
                                }
                            }  
                        }
                    }
                    if ( !empty( $result ) ) { 
                        if ($result->msg != ""){
                            echo '<div class="' . $result->class . '"><p>' . $result->msg . '</p></div>';
                        }
                    }  
                } 
            }
        }
    }

    /*
     * Request Api, Validate and sync data.
     * @since    1.0.0
     * @param    string    $accountid       The api account key.
     * @param    intiger    $post_id         The post id get from api.
    */
    public function wpconvurt_ConvurtApiPostData($accountid = "", $post_id= "")
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
            
        $errmsg   = "";
        $post_id  = json_encode($post_id);
        $url      = 'https://app.convurt.io/ws/getConvurtPosts';
        $data     = array(
            'accountid' => $accountid,
            'post_id'   => $post_id
        );
        $jsondata = json_encode($data);
        $response = wp_remote_post($url, array(
            'method'       => 'POST',
            'timeout'      => 45,
            'redirection'  => 5,
            'httpversion'  => '1.0',
            'blocking'     => true,
            'headers'      => array(),
            'body'         => $jsondata,
            'cookies'      => array()
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $status = json_decode($response['body'], true);
            if ($status['is_success'] == 0) {
                $returndata = array(
                    'flag'   => 0,
                    'msg'    => $status['message'],
                    'class'  => 'alert alert-danger'
                );
            }
            if ($status['is_success'] == 1) {
                $returndata = array(
                    'flag'    => 1,
                    'msg'     => $status['message'],
                    'class'   => 'alert alert-success',
                    'data'    => $status['data'],
                );
            }
            return json_encode($returndata);
        }
    }

    /*
     * Update Api Key Popup html
     * @since    1.0.0
     */
    public function wpconvurt_settings_update_popup()
    { ?>
        <div class="modal fade" id="myModalNorm" tabindex="-1" role="dialog" 
             aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <button type="button" class="close" 
                           data-dismiss="modal">
                               <span aria-hidden="true">&times;</span>
                               <span class="sr-only">Close</span>
                        </button>
                        <h4 class="modal-title" id="myModalLabel">
                            <?php _e( 'Update Settings', 'convurt' ); ?>
                        </h4>
                    </div>
                    
                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="status"></div>
                        <form role="form" method="post">

                          <div class="form-group">
                            <label for="accountid"><?php _e( 'Account Key:', 'convurt' ); ?></label>
                            <?php $convurt_api_user_option = get_option('convurt_api_user_option'); ?>
                              <input type="text" required autocomplete="off" class="form-control" value="<?php if($convurt_api_user_option != ""){ echo $convurt_api_user_option; } ?>" id="accountidupdate"/>
                          </div>

                         
                          <div class="form-group">
                            <label for="post_status">Select post type: </label>
                            <?php $convurt_post_type_option = get_option('convurt_post_type_option'); ?>
                            <select class="form-control selectpicker" id="post_type" name="post_type">
                                <option <?php if($convurt_post_type_option == "draft"){ echo 'selected="selected"'; } ?> value="draft">Draft</option>
                                <option <?php if($convurt_post_type_option == "publish"){ echo 'selected="selected"'; } ?> value="publish">Publish</option>
                            </select>
                        </div>
                          <?php wp_nonce_field( 'convurto_nonce_field', 'convurto_update_nonce_field_name' );?>
                          <input type="button" id="accsubmit" class="btn btn-info" value="<?php _e( 'Update', 'convurt' ); ?>" ><img class="loading" src="<?php echo plugins_url('admin/images/loading.gif', dirname(__FILE__)); ?>" style="display:none"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php    
    }

    /*
     * Update Account key using ajax request
     * @since    1.0.0
    */
    public function wpconvurt_update_account_key_request() {

        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // first check if data is being sent and that it is the data we want
        if(isset($_POST['post_type'])){
            
            update_option('convurt_post_type_option', $_POST['post_type']);
        }

        if ( isset( $_POST["accountid"] ) ) {

            $accountidkey  = sanitize_text_field($_POST['accountid']); // Its combination of text and numbers.
            $url           = 'https://app.convurt.io/ws/validateConvurtUser';
            $data          = array(
                'accountid' => $accountidkey
            );
            $jsondata = json_encode($data);
            $response = wp_remote_post($url, array(
                'method'       => 'POST',
                'timeout'      => 45,
                'redirection'  => 5,
                'httpversion'  => '1.0',
                'blocking'     => true,
                'headers'      => array(),
                'body'         => $jsondata,
                'cookies'      => array()
            ));
           
           if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                echo "Something went wrong: $error_message";
            } else {
                $status = json_decode($response['body'], true);
                if ($status['is_success'] == 0 ) {
                    $returndata = array(
                        'flag'       => 0,
                        'msg'        => $status['message'],
                        'alertclass' => 'alert alert-danger'
                    );
                }
                if ($status['is_success'] == 1) {
                    update_option('convurt_api_user_option',$status['data']['accountid']);
                    $returndata = array(
                        'flag'        => 1, 
                        'is_data'     => 1, 
                        'msg'         => $status['message'],
                        'alertclass'  => 'alert alert-success',
                        'accountid'   => $status['data']['accountid'],
                    );
                }
                echo json_encode($returndata);
            }
            die();
        }
    }

    /*
     * Update notice
     * @since    1.0.0
     */
    public function wpconvurt_update_notice()
    {
        if (get_option('wpconvurt_settings')) {
            $update_notice = '<div class="error notice">
                <p>
                    '.__( 'Thank you for updating to Convurt 1.0.0.', 'convurt' ).'
                    <br/><br/>
                    '.__( 'After update, It is recommended that you should ', 'convurt' ).'<strong>'.__( 'Deactivate the Convurt plugin and then Activate it again.', 'convurt' ).'</strong>
                </p>    
            </div>';
            echo $update_notice;
        }
    }
}