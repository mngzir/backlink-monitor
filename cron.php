<?php
require_once('../../../wp-load.php');

ini_set("log_errors", 1);
ini_set("error_log", "php-error.log");


global $wpdb;

$get_mngz_backlink_monitor_setting = get_option('mngz_backlink_monitor_setting_option_name');
$number_check = $get_mngz_backlink_monitor_setting['number_check_cron'];
$number_days = $get_mngz_backlink_monitor_setting['day_check_cron'];

$links = $wpdb->get_results("SELECT * ,DATEDIFF(NOW(),update_date) as diff FROM {$wpdb->prefix}mngz_backlinks WHERE DATEDIFF(NOW(),update_date) >= {$number_days} OR update_date = add_date LIMIT {$number_check}",ARRAY_A);
// echo"<pre>";
// print_r($links );
// print_r($wpdb->last_query);
// die();
if (!empty($links)) {
   foreach($links as $link){
        $link_id = $link['id'];
        $id = $link['domain_id'];
        $get_domain = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks_domain WHERE id = '{$id}'", ARRAY_A);
        $check_url = $link['link'];
        $check_domain = trim($get_domain[0]['domain']);
    
        $get_check_result = mngz_backlink_monitor_check_url($check_url,$check_domain);
        //echo"<pre die='ltr'>";
        //print_r($get_check_result);

        if($get_check_result['status'] ==200){
            $keywords = array();
            foreach($get_check_result['data']['links'] as $value){
            $keywords[] = $value['value'];
            }

            $update_result = $wpdb->update($wpdb->prefix.'mngz_backlinks',
                array(
                    'status_link' => $get_check_result['status'],
                    'title' => $get_check_result['data']['title'],
                    'keywords' => implode(',',$keywords),
                    'content' => json_encode($get_check_result['data']),
                    'update_date' => date("Y-m-d H:i:s")
                ),
                array('id' => $link_id)
            );
            echo urldecode($check_url)."<br><hr><br>";
        }else{
            $message = $get_check_result['message'];
            $update_result = $wpdb->update($wpdb->prefix.'mngz_backlinks',
                array(
                    'status_link' => $get_check_result['status'],
                    'title' => $message,
                    'update_date' => date("Y-m-d H:i:s")
                ),
                array('id' => $link_id)
            );
            echo urldecode($check_url).'<br>
            خطایی در دریافت اطلاعات از سمت curl رخ داده است  احتمال بلاک شدن ip هست چند دقیقه بعد امتحان نمایید.  
            <br>
            '.$message.'
            <br><hr><br>';  
        }
   }
 
}


