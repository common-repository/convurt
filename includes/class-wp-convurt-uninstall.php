<?php
/**
 * Fired during plugin unsintall
 *
 * @link       http://www.daffodilsw.com/
 * @since      1.0.0
 *
 * @package    Convurt
 * @subpackage Convurt/includes
 */

class Wp_Convurt_Uninstallor
{
    /**
     * 
     * @since    1.0.0
     */
    public static function uninstall()
    {
       if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

       // write your uninstall code
        $accountid  = get_option('convurt_api_user_option');
        $url        = 'https://app.convurt.io/ws/doUninstallPost';
        $data       = array(
            'accountid' => $accountid
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
            if ($status['is_success'] == 1) {
                $accountid   = $status['accountid'];
                $args        = array(
                    'numberposts'     => -1,
                    'post_type'       => 'post',
                    'meta_query'      => array(
                        array(
                            'key'     => 'voice_id',
                            'value'   => '',
                            'compare' => '!='
                        )
                    )
                );
                $posts_exist = new WP_Query($args);
                if ($posts_exist->have_posts()){
                    while ($posts_exist->have_posts()):
                        $posts_exist->the_post();
                        wp_delete_post(get_the_ID(), true);
                    endwhile;
                }
                $option_name = 'convurt_api_user_option';
                delete_option($option_name);
            }
            
        }  
            
    }
}
