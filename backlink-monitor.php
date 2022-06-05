<?php
/*
Plugin Name: بکلینک مانیتور
Plugin URI:  https://mngz.ir
Description: افزونه مانیتور بکلینک ها
Version:     1.0
Author:      Morteza Najaf Goli Zadeh
Author URI:  https://mngz.ir
*/
include_once "setting_page.php";
include_once "check_url.php";
define('MNGZ_BACKLINK_MONITOR_URL', plugin_dir_url(__FILE__));


function mngz_load_styles() {

    wp_register_style('link_admin_css',plugin_dir_url( __FILE__ )."assets/bootstrap/css/rtl/bootstrap.min.css");
    wp_enqueue_style('link_admin_css');

    wp_register_style( 'link_custom_style', false );
    wp_enqueue_style( 'link_custom_style' );
    $custom_css = "
            table {
            table-layout: fixed;
            word-wrap: break-word;
        }

        table th, table td {
            overflow: hidden;
        }
        .link-table table td,.link-table table th {
            font-size: 12px;
        }
        .modal {
            z-index: 1111111111111111 !important;
        }";
        wp_add_inline_style( 'link_custom_style', $custom_css );


        wp_register_script( "mngz_backlink_monitor_script", plugin_dir_url( __FILE__ )."assets/bootstrap/js/bootstrap.bundle.min.js", array('jquery') );
        wp_enqueue_script('mngz_backlink_monitor_script');
}


add_action('admin_menu', 'mngz_backlink_monitor_menu');
function mngz_backlink_monitor_menu() {
    $mngz_backlink_monitor_list = add_submenu_page( 'mngz_backlink_monitor', 'بکلینک ها', 'بکلینک ها','manage_options', 'mngz_backlink_monitor_list','mngz_backlink_monitor_list');
    $mngz_backlink_monitor_domain = add_submenu_page( 'mngz_backlink_monitor', 'دامنه ها', 'دامنه ها','manage_options', 'mngz_backlink_monitor_domain','mngz_backlink_monitor_domain');

	  add_action("load-{$mngz_backlink_monitor_list}", "mngz_load_styles");
	  add_action("load-{$mngz_backlink_monitor_domain}", "mngz_load_styles");
}


function mngz_backlink_monitor_domain()
{
    global $wpdb;

    if(isset($_GET['delete']) && !empty($_GET['delete'])){
        $id = trim($_GET['delete']);
        $get_result = $wpdb->delete( "{$wpdb->prefix}mngz_backlinks_domain", array( 'id' => $id ) );
        $wpdb->delete( "{$wpdb->prefix}mngz_backlinks", array( 'domain_id' => $id ) );
        if($get_result){
            echo'<div class="alert alert-success" role="alert">بدرستی حذف شد.</div>';
        }
    }

    if(isset($_POST['add_domain']) && $_POST['add_domain'] == "insert"){

        $domain = $_POST['domain'];
        $domain = trim($domain);
        $insert = false;
        if(!empty($domain)){
                $check_domain = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks_domain WHERE domain = '{$domain}'", ARRAY_A);
                //insert domain
                if(empty($check_domain)){
                    $insert_id = $wpdb->insert("{$wpdb->prefix}mngz_backlinks_domain",
                        array(
                            'domain'        => $domain
                        )
                    );
                    $insert = true;
                }
        }

        if($insert){
            echo'<div class="alert alert-success" role="alert">بدرستی اضافه شد.</div>';
        }else{
            echo'<div class="alert alert-danger" role="alert">خطایی رخ داده است.</div>';
        }
    }


    $domains = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks_domain ORDER BY id DESC", ARRAY_A);


   //print_r($link);
	?>


    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2>دامنه ها</h2><br>



                    <div class="card1">
                        <div class="card-header" id="headingTwo">
                            <div class="mb-0">
                                   ثبت دامنه جدید
                            </div>
                        </div>
                       <div class="card-body">
                                <form action="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_domain'); ?>" method="post">
                                    <input type="hidden" name="add_domain" value="insert">
                                    <div class="form-group">
                                            <label for="domains">دامنه را بدون www , http وارد نمایید (مثل: site.ir)</label>
                                            <input class="form-control" name="domain" dir="ltr" />
                                        </div>
                                    <div class="form-group">
                                            <input name="submit" type="submit" value="افزودن" class="button button-primary">
                                        </div>
                                </form>
                        </div>

                    </div>

            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col">
                <div class="link-table">
                    <table class="sortable table table-striped table-hover">
                    <thead>
                    <tr>
                        <th># </th>
                        <th>دامنه </th>
                        <th>عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i=0;
                            if(count($domains))
                            {
                                foreach($domains as $info)
                                {
                                    $i++;

                                    ?>
                                    <tr id="domain-<?php echo $info['id']; ?>">
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $info['domain']; ?></td>
                                        <td>
                                            <a class="btn btn-danger btn-sm" href="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_domain'); ?>&delete=<?php echo $info['id']; ?>" onclick="return confirm('با حذف دامنه تمام بکلینک های آن حذف خواهد شد آیا مطمئن هستید؟');">حذف</a>
                                            <a class="btn btn-info btn-sm" href="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_list'); ?>&id=<?php echo $info['id']; ?>">لیست بکلینک ها</a>
                                        </td>
                                    </tr>
                                    <?php

                                }
                            } else{
                                echo "<tr><td colspan=\"4\"> یافت نشد.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>

        </div>
    </div>

<?php
}



function mngz_backlink_monitor_list()
{
    global $wpdb;


    $id = 0;
    if(isset($_GET['id']) && !empty($_GET['id'])){
        $id = $_GET['id'];
    }

    if(isset($_GET['delete']) && !empty($_GET['delete'])){
        $delete_id = trim($_GET['delete']);
        $get_delete_result = $wpdb->delete( "{$wpdb->prefix}mngz_backlinks", array( 'id' => $delete_id ) );
        if($get_delete_result){
            echo'<div class="alert alert-success" role="alert">بدرستی حذف شد.</div>';
        }
    }

    if(isset($_GET['check_link']) && $_GET['check_link'] == "ok" && isset($_GET['link_id']) && !empty($_GET['link_id']) ){
        $link_id = $_GET['link_id'];
        $domain_id = $_GET['id'];
        $get_link = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks WHERE id = '{$link_id}'", ARRAY_A);
        $get_domain = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks_domain WHERE id = '{$domain_id}'", ARRAY_A);
        $check_url = $get_link[0]['link'];
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
            echo'<div class="alert alert-success" role="alert">اطلاعات با موفقیت دریافت شد.</div>';
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
            echo'<div class="alert alert-danger" role="alert">
             خطایی در دریافت اطلاعات از سمت curl رخ داده است  احتمال بلاک شدن ip هست چند دقیقه بعد امتحان نمایید.
             <br>
             '.$message.'
             </div>';
        }


    }

    if(isset($_POST['add_domain']) && $_POST['add_domain'] == "insert"){

        $domains = $_POST['domains'];
        $domain_id = $_POST['domain_id'];
        if($domain_id != 0) {
            $domains = explode("\n",$domains);
            $insert = false;
            foreach($domains as $link) {
                $link = trim($link);
                if(!empty($link)){
                    $link = mngz_backlink_monitor_addhttp($link);
                    $check_link = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks WHERE link = '{$link}' && domain_id = {$domain_id} ", ARRAY_A);
                    $date = date("Y-m-d H:i:s");
                    if(empty($check_link)){
                        $insert_id = $wpdb->insert("{$wpdb->prefix}mngz_backlinks",
                            array(
                                'domain_id'     => $domain_id,
                                'link'          => $link,
                                'add_date'      => $date,
                                'update_date'   => $date,
                                'status'        => 1,
                            )
                        );
                        $insert = true;
                    }
                }
            }
            if($insert){
                echo'<div class="alert alert-success" role="alert">بدرستی اضافه شد.</div>';
            }else{
                echo'<div class="alert alert-danger" role="alert">خطایی رخ داده است.</div>';
            }
        }else{
            echo'<div class="alert alert-danger" role="alert">لطفا یک دامنه انتخاب نمایید.</div>';
        }
    }


    if((isset($_GET['search_link']) && $_GET['search_link']=="done") && (!empty($_GET['link']) || !empty($_GET['status']) || !empty($_GET['keyword']))){

        $link = $_GET['link'];
        $status = $_GET['status'];
        $keyword = $_GET['keyword'];


        $where = "";
        if(!empty($link)){
            $where = " link like '%{$link}%' ";
        }
        if(!empty($status)){
            $where = " status_link like '%{$status}%' ";
        }
        if(!empty($keyword)){
            $where = " keywords like '%{$keyword}%' ";
        }


        if($id != 0 ){
            $link = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks WHERE {$where} AND domain_id = {$id} ORDER BY id DESC LIMIT 100000", ARRAY_A);
        }else{
            $link = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks WHERE {$where} ORDER BY id DESC LIMIT 100000", ARRAY_A);
        }

    }
    else{
        if($id != 0 ){
            $link = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks WHERE domain_id = {$id} ORDER BY id DESC LIMIT 100000", ARRAY_A);
        }else{
            $link = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks  ORDER BY id DESC LIMIT 100000", ARRAY_A);
        }
    }

    $domains = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks_domain", ARRAY_A);

   //print_r($domains);
	?>


    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h2>بکلینک ها</h2><br>
                <div class="alert alert-info" role="alert">
                    <?php
                    echo"تعداد کل بکلینک ها: ".count($link);
                    ?>
                </div>
                <div class="mb-3">
                <?php
                    if($id != 0 ){
                        $get_anchor_text = mngz_backlink_monitor_anchor_text($id);
                        arsort($get_anchor_text);
                        foreach($get_anchor_text as $key=>$value){
                            ?>
                            <a class="badge badge-primary" href="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_list'); ?>&search_link=done&id=<?php echo $id; ?>&link&status&keyword=<?php echo $key; ?>">
                                <?php echo $key; ?>
                                <span class="badge badge-light"><?php echo $value; ?></span>
                            </a>
                            <?php

                        }
                    }
                    ?>
                </div>
                <div class="accordion" id="accordionExample">

                    <div class="card1">
                        <div class="card-header" id="headingTwo">
                            <h2 class="mb-0">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                   ثبت لینک جدید
                                </button>
                            </h2>
                        </div>
                        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionExample">
                            <div class="card-body">
                                <form action="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_list'); ?>&id=<?php echo $id;?>" method="post">
                                    <input type="hidden" name="add_domain" value="insert">
                                    <div class="form-group">
                                        <label for="domain">دامنه</label>
                                        <select class="form-control" id="domain" name="domain_id">
                                        <option value="0">یک دامنه انتخاب کنید</option>
                                            <?php
                                                foreach ($domains as $domain){
                                                    $selected = "";
                                                    if($domain['id'] == $id){
                                                        $selected = "selected";
                                                    }
                                            ?>
                                                    <option value="<?php echo trim($domain['id']);?>" <?php echo $selected; ?> ><?php echo $domain['domain'];?></option>
                                            <?php
                                                }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                            <label for="domains">لینک ها را در هر سطر وارد کنید:</label>
                                            <textarea class="form-control" id="domains" name="domains" dir="ltr" cols="8" rows="8"></textarea>
                                    </div>
                                    <div class="form-group">
                                            <input name="submit" type="submit" value="افزودن" class="button button-primary">
                                        </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card1">
                        <div class="card-header" id="headingOne">
                            <h2 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    جستجو
                                </button>
                            </h2>
                        </div>

                        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordionExample">
                            <div class="card-body">

                                <form action="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_list'); ?>" method="get">
                                    <input type="hidden" name="page" value="mngz_backlink_monitor_list">
                                    <input type="hidden" name="search_link" value="done">
                                    <input type="hidden" name="id" value="<?php echo $id?>">

                                    <div class="form-row">
                                        <div class="form-group col-md-2">
                                            <label for="search-link">جستجو براساس لینک</label>
                                            <input type="text" name="link" class="form-control" id="search-link" value="<?php echo @$_GET['link'];?>">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="search-status">وضعیت لینک</label>
                                            <input type="text" class="form-control" id="search-status" name="status" value="<?php echo @$_GET['status'];?>">
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="search-keyword">کلمه کلیدی</label>
                                            <input type="text" class="form-control" id="search-keyword" name="keyword" value="<?php echo @$_GET['keyword'];?>">
                                        </div>

                                    </div>

                                    <button type="submit" class="btn btn-primary">جستجو</button>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <div class="row">
            <div class="col">
                <div class="link-table">
                    <table class="sortable table table-striped table-hover">
                    <thead>
                    <tr>
                        <th style="width: 8% !important;">دامنه </th>
                        <th style="width: 15% !important;">لینک</th>
                        <th style="width: 15% !important;">کلمات کلیدی</th>
                        <th>وضعیت لینک</th>
                        <th>عنوان</th>
                        <th style="width: 8% !important;">جزئیات </th>
                        <th>تاریخ بروز رسانی</th>
                        <th style="width: 20% !important;">عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php
                            $i=0;
                            if(count($link))
                            {
                                foreach($link as $info)
                                {
                                    $i++;

                                    ?>
                                    <tr id="domain-<?php echo $info['id']; ?>">
                                        <td><?php
                                        $domain_id = $info['domain_id'];
                                        $domain_name = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks_domain WHERE id = {$domain_id}", ARRAY_A);
                                        echo $domain_name[0]['domain'];
                                        ?></td>
                                        <td><a target="_blank" href="<?php echo $info['link']; ?>"><?php echo mngz_backlink_monitor_get_domain($info['link']); ?></a></td>
                                        <td><?php echo $info['keywords']; ?></td>
                                        <td><?php echo $info['status_link']; ?></td>
                                        <td><?php echo $info['title']; ?></td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#domain-edit-<?php echo $info['id']; ?>">
                                                مشاهده
                                            </button>
                                            <div class="modal fade" id="domain-edit-<?php echo $info['id']; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-xl" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="exampleModalLabel">جزئیات</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">

                                                         <?php
                                                            $content = json_decode($info['content'],1) ;
                                                            if(!empty($content)){
                                                                $keywords_all = array();
                                                                foreach($content['links'] as $value){
                                                                    $keywords_all[] = $value['value']." - (".urldecode($value['href']) .") - ".$value['rel'];
                                                                }

                                                                $meta_robots ="";
                                                                $meta_googlebot ="";
                                                                foreach($content['meta'] as $meta){
                                                                    if($meta['name'] =='robots' ){
                                                                        $meta_robots = $meta['content'];
                                                                    }
                                                                    if($meta['name'] =='googlebot' ){
                                                                        $meta_googlebot = $meta['content'];
                                                                    }

                                                                }
                                                        ?>
                                                            <div class="link-table">
                                                                    <table class="table table-striped table-hover">

                                                                        <tr>
                                                                            <th>عنوان</th>
                                                                            <td><?php echo $content['title']; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>لینک</th>
                                                                            <td style="direction: ltr;"><a target="_blank" href="<?php echo $info['link']; ?>"><?php echo urldecode($info['link']) ; ?></a></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>canonical</th>
                                                                            <td style="direction: ltr;"><?php echo $content['canonical']; ?></td>
                                                                        </tr>

                                                                        <tr>
                                                                            <th>status</th>
                                                                            <td><?php echo $info['status_link']; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>کلمات کلیدی</th>
                                                                            <td><?php echo implode("<br>",$keywords_all); ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>meta robot</th>
                                                                            <td><?php echo $meta_robots; ?></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th>meta googlebot</th>
                                                                            <td><?php echo $meta_googlebot; ?></td>
                                                                        </tr>
                                                                        <tr class="table-info">
                                                                            <th colspan="2">سایر meta</th>
                                                                        </tr>
                                                                        <?php
                                                                        foreach($content['meta'] as $meta1){
                                                                            if(!empty($meta1['name'])){
                                                                            ?>
                                                                            <tr>
                                                                                <th><?php echo $meta1['name'];?></th>
                                                                                <td><?php echo $meta1['content'];?></td>
                                                                            </tr>
                                                                            <?php
                                                                            }
                                                                        }
                                                                        ?>
                                                                    </table>
                                                            </div>
                                                        <?php } ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $info['update_date']; ?></td>
                                        <td>
                                            <a class="btn btn-warning btn-sm" href="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_list'); ?>&check_link=ok&link_id=<?php echo $info['id'];?>&id=<?php echo $info['domain_id'];?>">بررسی دستی</a>
                                            <a class="btn btn-danger btn-sm" href="<?php echo admin_url( 'admin.php?page=mngz_backlink_monitor_list'); ?>&delete=<?php echo $info['id']; ?>&id=<?php echo $info['domain_id'];?>" onclick="return confirm('آیا مطمئن هستید؟');">حذف</a>

                                        </td>
                                    </tr>
                                    <?php

                                }
                            } else{
                                echo "<tr><td colspan=\"4\"> یافت نشد.</td></tr>";
                            }
                        ?>
                    </tbody>
                </table>
                </div>
            </div>

        </div>
    </div>

<?php

}



function mngz_backlink_monitor_anchor_text($domain_id){
    global $wpdb;
    $links = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}mngz_backlinks WHERE domain_id = {$domain_id}", ARRAY_A);


    $keywords_all = array();
    foreach($links as $link){
        $keywords = explode(",",$link['keywords']);
        foreach($keywords as $keyword){
            $keyword = mngz_backlink_monitor_clean($keyword);
            if(!empty($keyword)){
                if($keywords_all[$keyword] == ""){
                    $keywords_all[$keyword] = 0;
                }

                $keywords_all[$keyword] = $keywords_all[$keyword]+1;
                //echo $keyword.$keywords_all[$keyword].'<br>';
            }
        }

    }

    return $keywords_all;

}

function mngz_backlink_monitor_clean($str)
{
    $string = htmlentities($str, null, 'utf-8');
    $content = str_replace("&nbsp;", "", $string);
    $content = html_entity_decode($content);
    $content = trim($content);
    return $content;
}

//install db
function mngz_backlink_monitor_install() {
    global $wpdb;
    $table1_name = $wpdb->prefix . 'mngz_backlinks';
    $table2_name = $wpdb->prefix . 'mngz_backlinks_domain';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE " . $table1_name . " (
            id bigint(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            domain_id bigint(10) NOT NULL,
            link text NULL,
            status_link int(10) NULL,
            keywords text COLLATE utf8_persian_ci NULL,
            title text COLLATE utf8_persian_ci NULL,
            content longtext COLLATE utf8_persian_ci NULL,
            description text COLLATE utf8_persian_ci NULL,
            add_date DATETIME NULL,
            update_date DATETIME NULL,
            status tinyint(1) NULL,
            PRIMARY KEY (id)
        ) ". $charset_collate .";"
        . "CREATE TABLE " . $table2_name . " (
            id bigint(10) NOT NULL AUTO_INCREMENT,
            domain varchar(500) COLLATE utf8_persian_ci NOT NULL,
            PRIMARY KEY (id)
        ) ". $charset_collate .";";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook(__FILE__,'mngz_backlink_monitor_install');



//helper
function mngz_backlink_monitor_addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://".$url;
    }
    return $url;
}

function mngz_backlink_monitor_get_domain($url=null) {
    preg_match("/^(https|http|ftp):\/\/(.*?)\//", "$url/" , $matches);
    $parts = explode(".", $matches[2]);
    $tld = array_pop($parts);
    $host = array_pop($parts);
    if ( strlen($tld) == 2 && strlen($host) <= 3 ) {
        $tld = "$host.$tld";
        $host = array_pop($parts);
    }

//    return array(
//        'protocol' => $matches[1],
//        'subdomain' => implode(".", $parts),
//        'domain' => "$host.$tld",
//        'host'=>$host,'tld'=>$tld
//    );
    return "$host.$tld";
}

//cron wp

// Scheduled Action Hook
function mngz_cron_daily_check( ) {
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
	            $log =  urldecode($check_url).'<br>
	            خطایی در دریافت اطلاعات از سمت curl رخ داده است  احتمال بلاک شدن ip هست چند دقیقه بعد امتحان نمایید.
	            <br>
	            '.$message.'
	            <br><hr><br>';
				
	        }
	   }

	}
}
add_action( 'mngz_cron_daily_check', 'mngz_cron_daily_check' );

// Custom Cron Recurrences
function mngz_cron_check_recurrence( $schedules ) {
	$schedules['5min'] = array(
		'display' => __( '5min', 'textdomain' ),
		'interval' => 300,
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'mngz_cron_check_recurrence' );

// Schedule Cron Job Event
function mngz_cron_check() {
	if ( ! wp_next_scheduled( 'mngz_cron_daily_check' ) ) {
		wp_schedule_event( time(), '5min', 'mngz_cron_daily_check' );
	}
}
add_action( 'wp', 'mngz_cron_check' );


?>