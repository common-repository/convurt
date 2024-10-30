<?php
/**
 * In this class we pocess cron functioning.
 * Automatic call function using cron.
 *
 * @since      1.0.0
 * @package    Convurt
 * @subpackage Convurt/includes
 * @author     Convurt, LLC@https://convurt.io/  
 */
class Wp_Convurt_Cron
{
    
    public function __construct()
    {
        // Set schedules for corn
        add_filter( 'cron_schedules', array( $this, 'wpconvurt_cron_add_minute' ) );

        // and make sure it's called whenever WordPress loads
        add_action( 'wp', array( $this, 'wpconvurt_cronstarter_activation' ) );

        // hook that function onto our scheduled event:
        add_action( 'wpconvurtjob', array( $this, 'wpconvurt_sync_automatic' ) );
    }

    /*
     * Adds once every minute to the existing schedules.
     * @since    1.0.0
    */
    public function wpconvurt_cron_add_minute($schedules)
    {
        // Adds once every minute to the existing schedules.
        $schedules['everyminute'] = array(
            'interval' => 60,
            'display' => __('Once Every Minute')
        );
        return $schedules;
    }
    
    /*
     * create a scheduled event (if it does not exist already).
     * @since    1.0.0
    */
    public function wpconvurt_cronstarter_activation()
    {
        if (!wp_next_scheduled('wpconvurtjob')) {
            wp_schedule_event(time(), 'everyminute', 'wpconvurtjob');
        }
    }

    /*
     * Request Api, Validate and sync data.
     * @since    1.0.0
     * @param    string    $accountid       The api account key.
     * @param    intiger   $post_id         The post id get from api.
    */
    public function wpconvurt_api_post_data_cron($accountid="", $post_id="")
    {
        $errmsg   = "";
        $post_id  = json_encode($post_id);
        $url      = 'https://app.convurt.io/ws/getConvurtPosts';
        $data     = array(
            'accountid' => $accountid,
            'post_id'   => $post_id
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
            if ($status['is_success'] == 0) {
                $returndata = array(
                    'flag'   => 0,
                    'msg'    => $status['message'],
                    'class'  => 'alert alert-danger'
                );
            }
            if ($status['is_success'] == 1) {
                $returndata = array(
                    'flag'   => 1,
                    'msg'    => $status['message'],
                    'class'  => 'alert alert-success',
                    'data'   => $status['data']
                );
            }
            return json_encode($returndata);
            
        }
    }

    /*
     * Send mail after import post.
     * @since    1.0.0
    */
    public function wpconvurt_send_mail($post_id){
        $blog_title  = get_bloginfo( 'name' );
        $admin_email = get_bloginfo('admin_email');
        if($admin_email != ""){
            $subject    = 'New post imported in '.$blog_title.'-PostID:'.$post_id;
            $message    = 'A new draft post has been imported from convurt';
            $message   .= 'Post ID: '.$post_id;

            // let's send it 
            wp_mail($admin_email, $subject, $message);
        }    
    }

    /*
     * Here's the function we'd like to call with our cron job.
     * @since    1.0.0
    */
    public function wpconvurt_sync_automatic()
    {
        $accountid = get_option('convurt_api_user_option');
        if (!empty($accountid)) {
            $post_id     = array();
            $args = array(
                  'post_type'      => 'post',
                  'order'          => 'ASC',
                  'posts_per_page' => -1,
                  'meta_query'     => array(
                      array(
                        'key'      => 'voice_id',
                        'value'    => '',
                        'compare'  => '!='
                        )
                      )
                );
            $posts_exist = new WP_Query($args);
            if ($posts_exist->have_posts()):
                while ($posts_exist->have_posts()):
                    $posts_exist->the_post();
                    $voice_id = get_post_meta(get_the_ID(), 'voice_id', true);
                    if(!in_array($voice_id, $post_id)){
                        array_push($post_id, $voice_id);
                    }    
                endwhile;
            endif;
            $Syncdata = $this->wpconvurt_api_post_data_cron($accountid, $post_id);

            $result   = json_decode($Syncdata);
            if($result->flag == 1){
                foreach ($result->data as $key => $value) {
                $convurt_post_type_option = get_option('convurt_post_type_option');

                  if($value->title != ""){
                     $args = array(
                       'post_type' => 'post',
                       'meta_query' => array(
                           array(
                               'key' => 'voice_id',
                               'value' => $value->post_id
                           )
                       ),
                        'fields' => 'ids'
                     );
                     // perform the query
                    $vid_query = new WP_Query( $args );

                    $vid_ids = $vid_query->posts;
                    
                     // do something if the meta-key-value-pair not exists in another post
                    if ( empty( $vid_ids ) ) {
                        $my_post = array(
                          'post_title'   => wp_strip_all_tags($value->title),
                          'post_content' => $value->voice_conversion_text,
                          'post_status'  => $convurt_post_type_option,
                          'post_author'  => get_current_user_id()
                        );
                        $post_id = wp_insert_post($my_post);
                        if ($post_id):
                            add_post_meta($post_id, 'voice_duration', $value->voice_duration);
                            add_post_meta($post_id, 'voice_id', $value->post_id);
                            //$this->wpconvurt_send_mail($post_id);
                        endif;
                    }
                      
                  }  
                }
            }    
        }
    }  
}
