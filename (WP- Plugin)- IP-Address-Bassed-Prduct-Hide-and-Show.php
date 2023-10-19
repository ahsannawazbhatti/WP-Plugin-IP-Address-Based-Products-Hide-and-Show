<?php
/*
Plugin Name: Product display according to location
Plugin URI: http://example.com
Description: Simple Contact Form Data Stored into Custom Post Type  
Version: 11.0
Author: Ahsan Nawaz Bhatti
*/

include_once("wp-config.php");
include_once("wp-includes/wp-db.php");

 $lhr_hide_products = array();
  $isl_hide_products= array();
  $krc_hide_products= array();
  
  // Getting User IP address

function getIP(){
if(!empty($_SERVER['HTTP_CLIENT_IP'])){
        //ip from share internet
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
        //ip pass from proxy
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

    // Filtering Hidden Products

add_filter( 'woocommerce_product_is_visible', 'hide_product_if_city', 9999, 2 );
function hide_product_if_city( $visible, $product_id ){
     $ip =  getIP(); // your ip address here
    $query = @unserialize(file_get_contents('http://ip-api.com/php/'.$ip));
    if($query && $query['status'] == 'success')
    {
        if( $query['city'] == "Lahore"||$query['city'] == "Islamabad"||$query['city'] == "Karachi" ){
            switch ($query['city']) {

              case "Lahore":{

                $lhr_hide_list= fetch_lhr_hide_products();
               foreach ($lhr_hide_list as $pid) {
              
              if($product_id == $pid ){
                      $visible = false;
                   }
                }
                 return $visible;
                  }

              case "Islamabad":{
                $isl_hide_list=fetch_isl_hide_products();
               foreach ($isl_hide_list as $pid) {
                            if ($product_id == $pid ){
                              $visible = false;
                           }
                }

                 return $visible;
                  }
              case "Karachi":{
                $krc_hide_list=fetch_krc_hide_products();
               foreach ($krc_hide_list as $pid) {
                            if ($product_id == $pid ){
                              $visible = false;
                           }
                }
                 return $visible;
                  }
        
              default:
                return $visible;
            }
        }
    }
   return $visible;
}



/*
-----
-------------------
--------
 */

function fetch_lhr_hide_products() {

    $lhr_hide_products1 =array();
    global $wpdb;
    $results = $wpdb->get_results( "SELECT post_id FROM wp_postmeta WHERE meta_key='_lahore_hide_meta_key' AND meta_value='Lahore'" );

    foreach ($results as $dat  ) {
    array_push($lhr_hide_products1,$dat->post_id);
    }

    return $lhr_hide_products1;
}

function fetch_isl_hide_products() {

    $isl_hide_products1 =array();
    global $wpdb;
    $results = $wpdb->get_results( "SELECT post_id FROM wp_postmeta WHERE meta_key='_islamabad_hide_meta_key' AND meta_value='Islamabad'" );

    foreach ($results as $dat  ) {
    array_push($isl_hide_products1,$dat->post_id);
    }

     return $isl_hide_products1;
}

function fetch_krc_hide_products() {

    $krc_hide_products1 =array();
    global $wpdb;
    $results = $wpdb->get_results( "SELECT post_id FROM wp_postmeta WHERE meta_key='_karachi_hide_meta_key' AND meta_value='Karachi'" );

    foreach ($results as $dat  ) {
    array_push($krc_hide_products1,$dat->post_id);
    }

     return $krc_hide_products1;
}

function cf_shortcode() {
    ob_start();

    cpt_form_code();
    return ob_get_clean();
}

add_shortcode( 'cpt_contact_form', 'cf_shortcode' );

## ---- 1. Backend ---- ##

    function wporg_add_custom_box() {
        $screens = [ 'product', 'post'];
        foreach ( $screens as $screen ) {
            add_meta_box(
                'wporg_box_id',                 // Unique ID
                ' Location Based Hide Products',      // Box title
                'wporg_custom_box_html',  // Content callback, must be of type callable
                $screen                            // Post type
            );
        }
    }

    function wporg_custom_box_html( $post ) {
        $value = get_post_meta( $post->ID, '_lahore_hide_meta_key', true );
        $value1 = get_post_meta( $post->ID, '_karachi_hide_meta_key', true );
        $value2 = get_post_meta( $post->ID, '_islamabad_hide_meta_key', true );
        ?>

        <label for='hide_porduct_1'> Hide In : </label>
        <select name='lahore_title_hide_porduct' id='hide_porduct_1' >
            <option value=''>- Select -</option>
            <option value='Lahore' <?php selected( $value, 'Lahore' ); ?>>Lahore</option>
        </select>

        <label for='hide_porduct_2'> Hide In : </label>
        <select name='karachi_title_hide_porduct' id='hide_porduct_2' >
            <option value=''>- Select -</option>
            <option value='Karachi'<?php selected( $value1, 'Karachi' ); ?>>Karachi</option>
        </select>

          <label for='hide_porduct_3'> Hide In : </label>
        <select name='islamabad_title_hide_porduct' id='hide_porduct_3' >
            <option value=''>- Select -</option>
            <option value='Islamabad'<?php selected( $value2, 'Islamabad'); ?>>Islamabad</option>
        </select>
        <?php
    }
    add_action( 'add_meta_boxes', 'wporg_add_custom_box' );

    function wporg_save_postdata( $post_id ) {
        // If Lahore is seclected
        if ( array_key_exists( 'lahore_title_hide_porduct', $_POST ) ) {
             $locationSelected1 = $_POST['lahore_title_hide_porduct'];
             update_post_meta(
                $post_id,
                '_lahore_hide_meta_key',
                $locationSelected1
            );

            }
            
            // If Karachi is seclected
         if ( array_key_exists( 'karachi_title_hide_porduct', $_POST ) ) {
             $locationSelected2 = $_POST['karachi_title_hide_porduct'];
             update_post_meta(
                $post_id,
                '_karachi_hide_meta_key',
                $locationSelected2
            );

            }

            // If Islamabad is seclected
         if ( array_key_exists( 'islamabad_title_hide_porduct', $_POST ) ) {
             $locationSelected3 = $_POST['islamabad_title_hide_porduct'];
             update_post_meta(
                $post_id,
                '_islamabad_hide_meta_key',
                $locationSelected3
            );

            }
    }
    add_action( 'save_post', 'wporg_save_postdata' );