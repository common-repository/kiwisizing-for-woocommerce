<?php
/*
 * Plugin Name:  KiwiSizing for WooCommerce
 * Plugin URI: https://www.kiwisizing.com/platforms/woocommerce
 * Description: Integrate Kiwi Sizing to your WooCommerce store. Help your customers shop the right size with powerful fit recommender and professional size charts. Improve your conversions and lower returns
 * Version: 1.9
 * Author: KiwiSizing
 * Author URI: https://www.kiwisizing.com/
 */

class WC_Settings_Tab_Kiwi {

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     */
    public static function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_settings_tab', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_settings_tab', __CLASS__ . '::update_settings' );
    }
    
    
    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     *
     * @param array $settings_tabs Array of WooCommerce setting tabs & their labels, excluding the Subscription tab.
     * @return array $settings_tabs Array of WooCommerce setting tabs & their labels, including the Subscription tab.
     */
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['settings_tab'] = __( 'KiwiSizing Integration', 'woocommerce-settings-tab-kiwi' );
        return $settings_tabs;
    }


    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     *
     * @uses woocommerce_admin_fields()
     * @uses self::get_settings()
     */
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }


    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     *
     * @uses woocommerce_update_options()
     * @uses self::get_settings()
     */
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }


    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     *
     * @return array Array of settings for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {

        $settings = array(
            'section_title' => array(
                'name'     => __( 'Kiwi Integration', 'woocommerce-settings-tab-kiwi' ),
                'type'     => 'title',
                'desc'     => 'This sets up basic information needed for the KiwiSizing integration. <a target="_blank" href="https://app.kiwisizing.com/admin/app">Open the KiwiSizing app</a> for more customization options.',
                'id'       => 'WC_Settings_Tab_Kiwi_section_title'
            ),
            'shop_id' => array(
                'name' => __( 'Shop ID', 'woocommerce-settings-tab-kiwi' ),
                'type' => 'text',
                'desc' => __( 'Enter KiwiSizing Shop ID', 'woocommerce-settings-tab-kiwi' ),
                'id'   => 'WC_Settings_Tab_Kiwi_Shop_ID'
            ),
            'injection_location' => array(
              'name' => __( 'Size chart link location (optional)', 'woocommerce-settings-tab-kiwi' ),
              'type' => 'select',
              'desc' => __( 'A quick way to control where to display size chart link on the product page using WooCommerce hooks. (see <a target="_blank" href="https://businessbloomer.com/woocommerce-visual-hook-guide-single-product-page/">this for a visual guide</a>).<br/><br/>For more flexibility, please directly use the <a target="_blank" href="https://help.kiwisizing.com/a/solutions/articles/44000554318">injection selector option</a> within the app.', 'woocommerce-settings-tab-kiwi' ),
              'id'   => 'WC_Settings_Tab_Kiwi_inject',
              'default' => 'woocommerce_before_add_to_cart_button',
              'options' => array(
                'woocommerce_before_add_to_cart_form' => 'Before add to cart form',
                'woocommerce_before_add_to_cart_button' => 'Before add to cart button',
                'woocommerce_after_add_to_cart_button' => 'After add to cart button',
                'woocommerce_after_add_to_cart_form' => 'After add to cart form',
                'woocommerce_product_meta_start' => 'product meta start',
                'woocommerce_product_meta_end' => 'product meta end',
                'woocommerce_after_single_product_summary' => 'After product summary',
              )
            ),
            'archive_inject' => array(
              'name' => __( 'Load size charts on archive / shop / category / tag pages', 'woocommerce-settings-tab-kiwi' ),
              'type' => 'select',
              'desc' => __( 'Note we don\'t recommend using this option since this may slow down the page as it tries to add size chart for every single product in the list. The exact location may be different depending on the WooCommerce theme', 'woocommerce-settings-tab-kiwi' ),
              'id'   => 'WC_Settings_archive_inject',
              'default' => 'no',
              'options' => array(
                'no' => 'Do not show',
                'woocommerce_shop_loop_item_title' => 'Before item title',
                'woocommerce_after_shop_loop_item_title' => 'After item title',
                'woocommerce_after_shop_loop_item' => 'After item',
              )
            ),
            'section_end' => array(
                 'type' => 'sectionend',
                 'id' => 'WC_Settings_Tab_Kiwi_section_end'
            )
        );

        return apply_filters( 'WC_Settings_Tab_Kiwi_settings', $settings );
    }

}

function get_orig_id($object_id, $type) {
  if (!has_filter('wpml_object_id') || !has_filter('wpml_default_language')) {
    return $object_id;
  }

  $default = apply_filters('wpml_default_language', NULL );
  if( is_array( $object_id ) ){
    $translated_object_ids = array();
    
    foreach ( $object_id as $id ) {
      $translated_object_ids[] = apply_filters('wpml_object_id', $id, $type, true, $default );
    }
    return $translated_object_ids;
  } else {
    return apply_filters( 'wpml_object_id', $object_id, $type, true, $default );
  }
}

function get_product_info($product) {
  
  $tags = get_the_terms( $product->get_id(), 'product_tag' );
  $tags_names = [];
  if (gettype($tags) == 'array') {
   $tags_names = array_map(function ($t) { return "'".addslashes($t->name)."'"; }, $tags);
  }

  $id = get_orig_id($product->get_id(), 'product');
  
  $categories = get_the_terms( $id, 'product_cat' );
  $categories_names = [];
  $categories_ids = [];
  if (gettype($categories) == 'array') {
   $categories_names = array_map(function ($c) { return "'".addslashes($c->name)."'"; }, $categories);
   $categories_ids = array_map(function ($c) { 
     return get_orig_id($c->term_id, 'product_cat'); 
    }, $categories);
   $categories_ids = array_map(function ($id) {return "'".$id."'";}, $categories_ids) ;
  }

  $image_ids = $product->get_gallery_image_ids();
  $images = array_map(function($i) {return wp_get_attachment_url($i);}, $image_ids);
  array_unshift($images,wp_get_attachment_url($product->get_image_id()));

  return array(
    'id' => addslashes($id),
    'name' => addslashes($product->get_name()),
    'sku' => addslashes($product->get_sku()),
    'categories_names' => $categories_names,
    'categories_ids' => $categories_ids,
    'tags' => $tags_names,
    'images' => $images,
  );
}

function load_kiwi_product_info_obj() {
  try {
    $product_id = get_queried_object_id();
    $product = wc_get_product( $product_id );
  } catch (Exception $e) {
    global $product;
  }
  
  $data = get_product_info($product);
  
  $images = array_map(function($url) {return "'".$url."'";}, $data['images']);

  echo '
  <script>
  window.KiwiSizing = window.KiwiSizing === undefined ? {} : window.KiwiSizing;
  window.KiwiSizing.data = {
    productID:"'.$data['id'].'",
    title:"'.$data['name'].'",
    sku:"'.$data['sku'].'",
    categories:['.join(",", array_merge($data['categories_names'], $data['categories_ids'])).'],
    tags:['.join(",", $data['tags']).'],
    images:['.join(",", $images).'],
  };
  </script>';
}

function load_kiwi_sizing_script() {
  $shop_id = get_option('WC_Settings_Tab_Kiwi_Shop_ID');

  // Insert kiwi sizing integration
  echo '<style>
  .ks-powered-by {display: none}
  </style>';

  echo '
      <!-- KiwiSizing v1.0.0 Integration !-->
      <script>
      !function(t,n,s,e,i){function r(t){try{var s="; "+n.cookie,e=s.split("; "+t+"=");if(2==e.length)return e.pop().split(";").shift()}catch(i){}return null}t[i]=t[i]||{},t[i]._queue=[];const o="on setShopID setUserID setUserEmail setLanguage loadSizing".split(" ");for(var a=0;a<o.length;a++){const c=o[a];t[i][c]=function(){var n=Array.prototype.slice.call(arguments);return n.unshift(c),t[i]._queue.push(n),t[i]}}const l=r("_ks_scriptVersion")||t.ks_version||"";var u=n.createElement(s);n=n.getElementsByTagName(s)[0],u.async=1,void 0!==t.ks_load_async&&(u.async=t.ks_load_async),u.src=e+"?v="+l,u.id="ks-integration",n.parentNode.insertBefore(u,n)}(window,document,"script","https://cdn.static.kiwisizing.com/SizingPlugin.prod.js?x=2","ks");
      </script>
      <!-- End KiwiSizing Integration !-->';

  if (is_user_logged_in()) {
    $current_user = wp_get_current_user();
    echo '
      <script>
      ks.setUserID("'.addslashes($current_user->ID).'");
      ks.setUserEmail("'.addslashes($current_user->user_email).'");
      </script>';
  }
          
  echo '
    <script>
    ks.setShopID("'.$shop_id.'");
    ks.loadSizing({
      productData: window.KiwiSizing ? window.KiwiSizing.data: {},
      options: {},
    });
    </script>';
}

function load_product_snippet() {
  global $product;
  $data = get_product_info($product);;

  echo '
  <!-- START KiwiSizing code !-->
<div id="KiwiSizingChart"
  class="kiwiAllowRegularInjectionSelector"
  data-product="'.htmlspecialchars($data['id']).'"
  data-sku="'.htmlspecialchars($data['sku']).'"
  data-categories="'.(join(",", array_merge($data['categories_names'], $data['categories_ids']))).'"
  data-tags="'.join(",", $data['tags']).'"
  data-product-name="'.addslashes($data['name']).'"
  data-product-images="'.htmlspecialchars(json_encode($data['images'])).'">
</div>';
}

// insert at the end of product page
function load_product_page_script() {
  if (is_product()) {
    add_action( 'wp_footer', 'load_kiwi_product_info_obj', 20 );
    add_action( 'wp_footer', 'load_kiwi_sizing_script', 30 );
  }
}
add_action( 'wp_head', 'load_product_page_script');
add_action( 'woocommerce_after_shop_loop', 'load_kiwi_sizing_script', 15 );

// insert kiwi place_holder
function load_kiwi_inject_placeholder() {
  echo '<div class="kiwi-sizing-placeholder"></div>';
}

function load_kiwi_inject() {
  $inject = get_option('WC_Settings_Tab_Kiwi_inject');

  $inject = $inject ? $inject : 'woocommerce_before_add_to_cart_button';

  add_action($inject, 'load_kiwi_inject_placeholder');
}
add_action( 'wp_head', 'load_kiwi_inject');

function load_kiwi_archive_inject() {
  $inject = get_option('WC_Settings_archive_inject');

  if ($inject !== null && $inject !== 'no') {
    add_action($inject, 'load_product_snippet'); 
  }
}

add_action( 'wp_head', 'load_kiwi_archive_inject');

// WC_Settings_archive_inject

WC_Settings_Tab_Kiwi::init();

// add plugin setting link on admin plugin page
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'salcode_add_plugin_page_settings_link');
function salcode_add_plugin_page_settings_link( $links ) {
  array_unshift($links, '<a href="' .
    admin_url( 'admin.php?page=wc-settings&tab=settings_tab' ) .
    '">' . __('Settings') . '</a>');
  return $links;
}
