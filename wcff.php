<?php
/**
 * 
 * Plugin Name: WC Fields Factory
 * Plugin URI: http://sarkware.com/wc-fields-factory-a-wordpress-plugin-to-add-custom-fields-to-woocommerce-product-page/
 * Description: It allows you to add custom fields to your woocommerce product page. You can add custom fields and validations without tweaking any of your theme's code & templates, It also allows you to group the fields and add them to particular products or for particular product categories. Supported field types are text, numbers, email, textarea, checkbox, radio and select.
 * Version: 3.0.4
 * Author: Saravana Kumar K
 * Author URI: http://www.iamsark.com/
 * License: GPL
 * Copyright: sarkware
 * WC tested up to: 4.3.1
 */
if (!defined( 'ABSPATH' )) { exit; }

/**
 * 
 * WC Fields Factory's Main Class
 * 
 * @author 		Saravana Kumar K
 * @copyright 	Sarkware Research & Development (OPC) Pvt Ltd
 *
 */

include_once('includes/wcff_loader.php');

class wcff {
    
    var
       /* Version number and root path details - could be accessed by "wcff()->info" */
        $info,
        /* Data Access Object reference - could be accessed by "wcff()->dao" */
        $dao,
        /* Fields interface - could be accessed by "wcff()->field" */
        $field,
        /* Fields injector instance - could be accessed by "wcff()->injector" */
        $injector,
        /* Fields Persister instance (which mine the REQUEST object and store the custom fields as Cart Item Data) - could be accessed by "wcff()->persister" */
        $persister,
        /* Fields Data Renderer instance - on Cart & Checkout - could be accessed by "wcff()->renderer" */
        $renderer,
        /* Fields Editor instance - on Cart & Checkout (though editing option won't works on Checkout) - could be accessed by "wcff()->editor" */
        $editor,
        /* Used to split the cart item (if the quantity is more than one and cloning is enabled) */
        $splitter,
        /* Pricing & Fee handler instance - could be accessed by "wcff()->negotiator" */
        $negotiator,
        /* Order handler instance - could be accessed by "wcff()->order" */
        $order,
        /* Option object - could be accessed by "wcff()->option" */
        $option,
        /* Html builder object reference - could be accessed by "wcff()->builder" */
        $builder,
        /* Fields Validator instance - could be accessed by "wcff()->validator" */
        $validator,
        /* Fields Translator instance - could be accessed by "wcff()->locale" */
        $locale,
        /* Holds the Ajax request object comes from WC Fields Factory Admin Interfce - could be accessed by "wcff()->request" */
        $request,
        /* Holds the Ajax response object which will be sent back to Client - could be accessed by "wcff()->response" */
        $response,
        /**/
        $loaded = false;
        
    public function __construct() {
        /* Put some most wanted values on info property */
        $this->info = array(
        	'dir'				=> plugin_dir_url(__FILE__),
            'path'				=> plugin_dir_path(__FILE__),
        	'assets'			=> plugin_dir_url(__FILE__) ."assets",
        	'views'				=> plugin_dir_path(__FILE__) ."views",
        	'inc'				=> plugin_dir_path(__FILE__) ."includes",            
            'basename'          => plugin_basename(__FILE__),
            'version'			=> '4.0.0'
        );
        
    }
    
    public function init() {     
        $loader = new wcff_loader($this);
        add_action('init', array($loader, 'load'), 1);
    }
        
}

/**
 *
 * Returns the Main instance of WC Fields Factory
 *
 * Helper function for accessing Fields Factory Globally
 * Using this function other plugins & themes can access the WC Fields Factory. thus no need of Global Variable.
 *
 */
function wcff() {
    /* Expose WC Fields Factory to Global Space */
    global $wcff;
    /* Singleton instance of WC Fields Factory */
    if (!isset($wcff)) {
        $wcff = new wcff();
        $wcff->init();
    }
    return $wcff;
}

/* Well use 'plugins_loaded' hook to start WC Fields Factory */
wcff();

?>