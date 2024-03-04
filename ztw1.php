<?php
/**
 * @package ztw1
 * @version 1
 */
/*
Plugin Name: ztw1
Plugin URI: http://wordpress.org/plugins/ztw1/
Description: This plugins adds ad between title and post
Author: Jakub Cebula, Jakub Kozanecki
Version: 1
Author URI: localhost
*/

function ztw1_register_styles()
{
    //register style
    wp_register_style('ztw1_styles', plugins_url('/css/style.css', __FILE__));
    //enable style (load in meta of html)
    wp_enqueue_style('ztw1_styles');
}

add_action('init', 'ztw1_register_styles');

function ztw1_admin_actions_register_menu()
{
    add_options_page("ZTW1", "ZTW", 'manage_options', "ztw1", "ztw1_admin_page");
}


add_action('admin_menu', 'ztw1_admin_actions_register_menu');

function create_ads_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advertise_ads';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        content text NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

register_activation_hook( __FILE__, 'create_ads_table' );


function get_advertisements() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advertise_ads';

    return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
}

function add_advertisement($ad) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'advertise_ads';

    $wpdb->insert($table_name, array('content' => $ad), array('%s'));
}


function ztw1_admin_page(){
    // get _POST variable from globals
    global $_POST;
    // process changes from form
    if (isset($_POST['ztw1_do_change'])) {
        if ($_POST['ztw1_do_change'] == 'Y') {
            $new_ad = $_POST['ztw1_new_ad'];
            echo '<div class="notice notice-success isdismissible">
<p>Advertisement saved.</p></div>';
            //update_option('ztw1_new_ad', $new_ad);
            add_advertisement($new_ad);
            update_option('ztw1_ads', get_advertisements());
        }
    }

    //display admin page
    ?>
    <div class="wrap">
        <h1>Ad creator</h1>
        <form name="ztw1_form" method="post">
            <input type="hidden" name="ztw1_do_change" value="Y">
            <p>Insert html to be displayed as ad:<br>
                <textarea name="ztw1_new_ad" maxlength="65536" required></textarea>
            </p>
            <p class="submit"><input type="submit" value="Submit"></p>
        </form>
    </div>
    <div class="wrap">
        <p>Current ads:</p>
        <?php
        $ad_arr = get_option('ztw1_ads');
        //show existing ads
        foreach ($ad_arr as $row){
            echo $row['content'];
        }
        ?>
    </div>
    <?php
}


function ztw1_add_ad_after_post_title($content, $id)
{
    $all_ads = get_option('ztw1_ads');
    //get setting for how long post is a new post
    if (!empty($all_ads)) {
        $random_index = array_rand($all_ads);
        $random_ad = $all_ads[$random_index]['content'];
    } else {
        // Brak wyników
        //echo 'Brak dostępnych ogłoszeń.';
    }
//    $random_ad = $all_ads[rand(1, count($all_ads['content']))]['content'];
    //generate proper post title
    return $content . "<br><div class='ad'>" . $random_ad . "</div>";
}

add_filter("the_title", "ztw1_add_ad_after_post_title", 10, 2);



