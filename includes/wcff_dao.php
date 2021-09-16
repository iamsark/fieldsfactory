<?php

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 *
 * Data access layer for WC Fields Factory
 *
 * @author Saravana Kumar K
 * @copyright Sarkware Research & Development (OPC) Pvt Ltd
 *
 */
class wcff_dao {
	
	/* Namespace for WCFF related post meta
	 * "wccpf_" for Custom product page Fields ( Front end product page )
	 * "wccaf_" for Custom admin page fields ( for Admin Products )
	 * "wccvf_" for Custom admin page fields ( for Variation Fields )
	 * "wcccf_" for Custom admin page fields ( for Checkout Fields )
	 *  */
	private $wcff_key_prefix = "wccpf_";
	
	/* Holds all the supported field's specific configuration meta */
	private $fields_meta = array();
	
	/* Holds all the configuration meta that are common to all fields ( both Product as well as Admin ) */
	private $common_meta = array();
	
	/* Holds all the configuration meta that are common to Admin Fields */
	private $wccaf_common_meta = array();
	
	/**/
	public $special_keys = array(
		'fee_rules',
	    'layout_meta',
		'field_rules',
		'group_rules',
		'pricing_rules',
		'location_rules',
		'condition_rules',	
	    'show_group_title',
	    'use_custom_layout',
		'product_tab_title',
		'product_tab_priority',
		'is_this_group_clonable',
	    'fields_label_alignement',
		'field_location_on_product',
		'field_location_on_archive',	   
	    'is_this_group_for_authorized_only',
	    'wcff_group_preference_target_roles'
	);
	
	public function __construct() {
		/* Wordpress's Save Post action hook
		 * This is where we would save all the rules for the Fields Group ( post ) that is being saved */
		add_action( 'save_post', array($this, 'on_save_post' ), 1, 3 );
	}
	
	/**
	 *
	 * Set the current post type properties,<br/>
	 * based on this only all the subsequent fields related operation will happen<br/>
	 * this option could be either 'wccpf' for product fields or 'wccaf' for admin fields.
	 *
	 * @param string $_type
	 *
	 */
	public function set_current_post_type($_type = "wccpf") {
		$this->wcff_key_prefix = $_type . "_";
	}
	
	/**
	 *
	 * Return the Fields config meta for Factory View<br/>
	 * Contains entire (specific to each fields) config meta list for each field type.
	 *
	 * @return array
	 *
	 */
	public function get_fields_meta() {
		/* Make sure the meta is loaded */
		$this->load_core_meta();
		return $this->fields_meta;
	}
	
	/**
	 *
	 * Return the Fields config common meta for Factory View<br/>
	 * Contains entire (common for all fields) config meta list for each field type.
	 *
	 * @return array
	 *
	 */
	public function get_fields_common_meta() {
		/* Make sure the meta is loaded */
		$this->load_core_meta();
		return $this->common_meta;
	}
	
	/**
	 *
	 * Return the Admin Fields config common meta for Factory View<br/>
	 * Contains entire (common for all admin fields) config meta list for each field type.
	 *
	 * @return array
	 *
	 */
	public function get_admin_fields_comman_meta() {
		/* Make sure the meta is loaded */
		$this->load_core_meta();
		return $this->wccaf_common_meta;
	}
	
	/**
	 *
	 * Loads Fields configuration meta from the file system<br>
	 * Fields specific configuration meta from 'meta/wcff-meta.php'<br>
	 * Common configuration meta from 'meta/wcff-common-meta.php'<br>
	 * Common admin configuration meta from 'meta/wcff-common-wccaf-meta.php'
	 *
	 */
	private function load_core_meta() {
		/* Load core fields config meta */
		if (!is_array($this->fields_meta) || empty( $this->fields_meta)) {
			$this->fields_meta = include('meta/wcff-meta.php');
		}
		/* Load common config meta for all fields */
		if (!is_array($this->common_meta) || empty($this->common_meta)) {
			$this->common_meta = include('meta/wcff-common-meta.php');
		}
		/* Load common config meta for admin fields */
		if (!is_array($this->wccaf_common_meta) || empty($this->wccaf_common_meta)) {
			$this->wccaf_common_meta = include('meta/wcff-common-wccaf-meta.php');
		}
	}
	
	/**
	 *
	 * Called whenever user 'Update' or 'Save' post from wp-admin single post view<br/>
	 * This is where the various (Product, Cat, Location ... ) rules for the fields group will be stored in their respective post meta.
	 *
	 * @param integer $_pid
	 * @param WP_Post $_post
	 * @param boolean $_update
	 * @return void|boolean
	 *
	 */
	public function on_save_post($_pid = 0, $_post, $_update) {
		/* Maje sure the post types are valid */
		if (!$_pid || ! $_post || ($_post->post_type != "wccpf" && $_post->post_type != "wccaf" && $_post->post_type != "wccvf")) {
			return false;
		}
		
		$_pid = absint( $_pid );
		
		/* Prepare the post type prefix for meta key */
		$this->wcff_key_prefix = $_post->post_type . "_";
		
		/* Conditional rules - determine which fields group belongs to which products */
		if (isset($_REQUEST["wcff_condition_rules"])) {
			delete_post_meta($_pid, $this->wcff_key_prefix .'condition_rules');
			add_post_meta($_pid, $this->wcff_key_prefix .'condition_rules', $_REQUEST["wcff_condition_rules"]);
		}
		
		/* Location rules - specific to Admin Fields */
		if (isset($_REQUEST["wcff_location_rules"])) {
			delete_post_meta($_pid, $this->wcff_key_prefix .'location_rules');
			add_post_meta($_pid, $this->wcff_key_prefix .'location_rules', $_REQUEST["wcff_location_rules"]);
		}
		
		/**/
		if (isset($_REQUEST["wcff_layout_meta"])) {
		    delete_post_meta($_pid, $this->wcff_key_prefix .'layout_meta');
		    add_post_meta($_pid, $this->wcff_key_prefix .'layout_meta', $_REQUEST["wcff_layout_meta"]);
		}
		
		if (isset($_REQUEST["wcff_use_custom_layout"])) {		    
		    delete_post_meta($_pid, $this->wcff_key_prefix .'use_custom_layout');
		    add_post_meta($_pid, $this->wcff_key_prefix .'use_custom_layout', "yes");
		} else {
		    delete_post_meta($_pid, $this->wcff_key_prefix .'use_custom_layout');
		    add_post_meta($_pid, $this->wcff_key_prefix .'use_custom_layout', "no");
		}
		
		/* Field location for each field's group */
		if (isset($_REQUEST["field_location_on_product"])) {
			delete_post_meta($_pid, $this->wcff_key_prefix .'field_location_on_product');
			add_post_meta($_pid, $this->wcff_key_prefix .'field_location_on_product', $_REQUEST["field_location_on_product"]);
			delete_post_meta($_pid, $this->wcff_key_prefix .'product_tab_title');
			delete_post_meta($_pid, $this->wcff_key_prefix .'product_tab_priority');
			if ($_REQUEST["field_location_on_product"] == "woocommerce_single_product_tab" && isset($_REQUEST["product_tab_config_title"])) {
				add_post_meta($_pid, $this->wcff_key_prefix .'product_tab_title', $_REQUEST["product_tab_config_title"]);
				add_post_meta($_pid, $this->wcff_key_prefix .'product_tab_priority', $_REQUEST["product_tab_config_priority"]);
			}
		}
		
		/* Field location for archive page */
		if (isset($_REQUEST["field_location_on_archive"])) {
			delete_post_meta($_pid, $this->wcff_key_prefix .'field_location_on_archive');
			add_post_meta($_pid, $this->wcff_key_prefix .'field_location_on_archive', $_REQUEST["field_location_on_archive"]);
		}
		
		/* Group level cloning option */
		if (isset($_REQUEST["wcff_group_clonable_radio"])) {
			delete_post_meta($_pid, $this->wcff_key_prefix .'is_this_group_clonable');
			add_post_meta($_pid, $this->wcff_key_prefix .'is_this_group_clonable', $_REQUEST["wcff_group_clonable_radio"]);			
		}
		
		/* Group title display option */
		if (isset($_REQUEST["wcff_group_title_radio"])) {
		    delete_post_meta($_pid, $this->wcff_key_prefix .'show_group_title');
		    add_post_meta($_pid, $this->wcff_key_prefix .'show_group_title', $_REQUEST["wcff_group_title_radio"]);
		}
		
		/**/
		if (isset($_REQUEST["wcff_label_alignment_radio"])) {
		    delete_post_meta($_pid, $this->wcff_key_prefix .'fields_label_alignement');
		    add_post_meta($_pid, $this->wcff_key_prefix .'fields_label_alignement', $_REQUEST["wcff_label_alignment_radio"]);
		}
		
		/* Authorized users only option */
		if (isset($_REQUEST["wcff_group_authorized_only_radio"])) {		    
		    delete_post_meta($_pid, $this->wcff_key_prefix .'is_this_group_for_authorized_only');
		    add_post_meta($_pid, $this->wcff_key_prefix .'is_this_group_for_authorized_only', $_REQUEST["wcff_group_authorized_only_radio"]);		
		}
		
		/* Target roles option */
		if (isset($_REQUEST["wcff_group_preference_target_roles"])) {
		    delete_post_meta($_pid, $this->wcff_key_prefix .'wcff_group_preference_target_roles');
		    add_post_meta($_pid, $this->wcff_key_prefix .'wcff_group_preference_target_roles', json_encode($_REQUEST["wcff_group_preference_target_roles"]));	
		}
		
		/* Update the fields order */
		$this->update_fields_order($_pid);
		
		return true;
	}
	
	/**
	 *
	 * Update the fields sequence order properties for all fields on a given group (represented by $_pid)<br/>
	 * Called when Fields Group got saved or updated.
	 *
	 * @param integer $_pid
	 * @return boolean
	 *
	 */
	public function update_fields_order($_pid = 0) {
		$fields = $this->load_fields($_pid, false);
		/* Update each fields order property */
		foreach ($fields as $key => $field) {
			if (isset($_REQUEST[$key."_order"])) {
				$field["order"] = $_REQUEST[$key."_order"];
				update_post_meta($_pid, $key, wp_slash(json_encode($field)));
			}
		}
		
		return true;
	}
	
	/**
	 *
	 * Load conditional rules for given Fields Group Post
	 *
	 * @param integer $_pid
	 * @return mixed
	 *
	 */
	public function load_target_products_rules($_pid = 0) {
		$_pid = absint( $_pid );
		/* Since we have renamed 'group_rules' meta as 'condition_rules' we need to make sure it is upto date
		 * and we remove the old 'group_rules' meta as well
		 **/
		$rules = get_post_meta($_pid, $this->wcff_key_prefix .'group_rules', true);
		if ($rules && $rules != "") {
			delete_post_meta($_pid, $this->wcff_key_prefix .'group_rules');
			update_post_meta($_pid, $this->wcff_key_prefix .'condition_rules', $rules);
		}
		$condition = get_post_meta($_pid, $this->wcff_key_prefix .'condition_rules', true);
		if ($condition != "") {
		    $condition = json_decode($condition, true);
		} else {
		    $condition = array();
		}
		
		return apply_filters($this->wcff_key_prefix .'condition_rules', $condition, $_pid);
	}
	
	public function load_layout_meta($_pid = 0) {
	    $_pid = absint($_pid);
	    $layout = get_post_meta($_pid, $this->wcff_key_prefix .'layout_meta', true);
	    if ($layout != "") {
	        $layout = json_decode($layout, true);
	    } else {
	        $layout = array();
	    }
	    return apply_filters($this->wcff_key_prefix .'layout_meta', $layout, $_pid);
	}
	
	public function load_use_custom_layout($_pid) {
	    $_pid = absint($_pid);
	    $use_custom_layout = get_post_meta($_pid, ($this->wcff_key_prefix ."use_custom_layout"), true);
	    return ($use_custom_layout != "") ? $use_custom_layout : "no";
	}
	
	/**
	 *
	 * Load locational rules for given Admin Fields Group Post
	 *
	 * @param integer $_pid
	 * @return mixed
	 *
	 */
	public function load_location_rules($_pid = 0) {
		$_pid = absint($_pid);
		$location = get_post_meta($_pid, $this->wcff_key_prefix .'location_rules', true);
		return apply_filters( $this->wcff_key_prefix .'location_rules', $location, $_pid );
	}
	
	/**
	 *
	 * Load locational rules for entire admin fields posts
	 *
	 * @return mixed
	 *
	 */
	public function load_all_wccaf_location_rules() {
		$location_rules = array();
		$wcffs = get_posts(array(
			'post_type' => "wccaf",
			'posts_per_page' => -1,
			'order' => 'ASC')
		);
		if (count($wcffs) > 0) {
			foreach ($wcffs as $wcff) {
				$temp_rules = get_post_meta($wcff->ID, 'wccaf_location_rules', true);
				$temp_rules = json_decode($temp_rules, true);
				$location_rules = array_merge($location_rules, $temp_rules);
			}
		}
		
		return apply_filters( 'wccaf_all_location_rules', $location_rules );
	}
	
	/**
	 *
	 * Used to load all woocommerce products<br/>
	 * Used in "Conditions" Widget
	 *
	 * @return 	ARRAY of products ( ids & titles )
	 *
	 */
	public function load_all_products() {
		$productsList = array();
		$products = get_posts(array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'order' => 'ASC')
		);
		
		if (count($products) > 0) {
			foreach ($products as $product) {
				$productsList[] = array("id" => $product->ID, "title" => $product->post_title);
			}
		}
		
		return apply_filters( 'wcff_products', $productsList );
	}
	
	/**
	 *
	 * Used to load all woocommerce products<br/>
	 * Used in "Conditions" Widget
	 *
	 * @return 	ARRAY of products ( ids & titles )
	 *
	 */
	public function load_variable_products() {
		$productsList = array();
		$products = get_posts(array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'order' => 'ASC')
		);
		
		if (count($products) > 0) {
			$wcG3 = version_compare(WC()->version, '2.2.0', '<');
			foreach ($products as $product) {
				$product_ob = $wcG3 ? get_product($product->ID) : wc_get_product($product->ID);				
				if ($product_ob->is_type( 'variable' )){
					$productsList[] = array("id" => $product->ID, "title" => $product->post_title);
				}
			}
		}
		
		return apply_filters( 'wcff_products_with_variation', $productsList );
	}
	
	/**
	 *
	 * Used to load all woocommerce product category<br/>
	 * Used in "Conditions" Widget
	 *
	 * @return 	ARRAY of product categories ( ids & titles )
	 *
	 */
	public function load_product_categories() {
		$product_cats = array();
		$pcat_terms = get_terms('product_cat', 'orderby=count&hide_empty=0');
		
		foreach ($pcat_terms as $pterm) {
			$product_cats[] = array("id" => $pterm->slug, "title" => $pterm->name);
		}
		
		return apply_filters( 'wcff_product_categories', $product_cats );
	}
	
	/**
	 *
	 * Used to load all woocommerce product tags<br/>
	 * Used in "Conditions" Widget
	 *
	 * @return 	ARRAY of product tags ( ids & titles )
	 *
	 */
	public function load_product_tags() {
		$product_tags = array();
		$ptag_terms = get_terms('product_tag', 'orderby=count&hide_empty=0');
		
		foreach ($ptag_terms as $pterm) {
			$product_tags[] = array("id" => $pterm->slug, "title" => $pterm->name);
		}
		
		return apply_filters( 'wcff_product_tags', $product_tags );
	}
	
	/**
	 *
	 * Used to load all woocommerce product types<br/>
	 * Used in "Conditions" Widget
	 *
	 * @return 	ARRAY of product types ( slugs & titles )
	 *
	 */
	public function load_product_types() {
		$product_types = array();
		$all_types = array (
			'simple'   => __( 'Simple product', 'woocommerce' ),
			'grouped'  => __( 'Grouped product', 'woocommerce' ),
			'external' => __( 'External/Affiliate product', 'woocommerce' ),
			'variable' => __( 'Variable product', 'woocommerce' )
		);
		
		foreach ($all_types as $key => $value) {
			$product_types[] = array("id" => $key, "title" => $value);
		}
		
		return apply_filters( 'wcff_product_types', $product_types );
	}
	
	/**
	 *
	 * Used to load all woocommerce product types<br/>
	 * Used in "Conditions" Widget
	 *
	 * @return 	ARRAY of product types ( slugs & titles )
	 *
	 */
	public function load_product_variations($parent = 0) {
		$products_variation_list = array();
		$variations = array();
		$arg = array (
			'post_type' => 'product_variation',
			'posts_per_page' => -1,
			'order' => 'ASC'
		);
		if ($parent != 0) {
			$arg['post_parent']  = $parent;
		}
		$variations = get_posts($arg);
		foreach ($variations as $product) {
			$products_variation_list[] = array("id" => $product->ID, "title" => $product->post_title);
		}
		return apply_filters( 'wcff_product_variations', $products_variation_list );
	}
	
	/**
	 *
	 * Used to load all woocommerce product tabs<br/>
	 * Used in "Location" Widget
	 *
	 * @return 	ARRAY of product tabs ( titles & tab slugs )
	 *
	 */
	public function load_product_tabs() {
		return apply_filters( 'wcff_product_tabs', array (
			"General Tab" => "woocommerce_product_options_general_product_data",
			"Inventory Tab" => "woocommerce_product_options_inventory_product_data",
			"Shipping Tab" => "woocommerce_product_options_shipping",
			"Attributes Tab" => "woocommerce_product_options_attributes",
			"Related Tab" => "woocommerce_product_options_related",
			"Advanced Tab" => "woocommerce_product_options_advanced",
			"Variable Tab" => "woocommerce_product_after_variable_attributes"
		));
	}
	
	/**
	 *
	 * Used to load all wp context used for meta box<br/>
	 * Used for laying Admin Fields
	 *
	 * @return 	ARRAY of meta contexts ( slugs & titles )
	 *
	 */
	public function load_metabox_contexts() {
		return apply_filters( 'wcff_metabox_contexts', array (
			"normal" => __( "Normal", "wc-fields-factory" ),
			"advanced" => __( "Advanced", "wc-fields-factory" ),
			"side" => __( "Side", "wc-fields-factory" )
		));
	}
	
	/**
	 *
	 * Used to load all wp priorities used for meta box<br/>
	 * Used for laying Admin Fields
	 *
	 * @return 	ARRAY of meta priorities ( slugs & titles )
	 *
	 */
	public function load_metabox_priorities() {		
		return apply_filters( 'wcff_metabox_priorities', array (
			"low" => __( "Low", "wc-fields-factory" ),
			"high" => __( "High", "wc-fields-factory" ),
			"core" => __( "Core", "wc-fields-factory" ),
			"default" => __( "Default", "wc-fields-factory" )
		));
	}
	
	/**
	 *
	 * Used to load all woocommerce form fields validation types, to built Checkout Fields
	 *
	 * @return ARRAY of validation types
	 *
	 */
	public function load_wcccf_validation_types() {
		return apply_filters( 'wcccf_validation_types', array (
			"required" => __( "Required", "wc-fields-factory" ),
			"phone" => __( "Phone", "wc-fields-factory" ),
			"email" => __( "Email", "wc-fields-factory" ),
			"postcode" => __( "Post Code", "wc-fields-factory" )
		));
	}
	
	public function load_target_contexts() {
	    return apply_filters( "wcff_target_context", array (
	        array("id" => "product", "title" => __("Product", "wc-fields-factory")),
	        array("id" => "product_cat", "title" => __("Product Category", "wc-fields-factory")),
	        array("id" => "product_tag", "title" => __("Product Tag", "wc-fields-factory")),
	        array("id" => "product_type", "title" => __("Product Type", "wc-fields-factory")),
	        array("id" => "product_variation", "title" => __("Product Variation", "wc-fields-factory"))
	    ));
	}
	
	public function load_target_logics() {
	    return apply_filters( "wcff_target_logic", array (
	        array("id"=>"==", "title"=>__("is equal to", "wc-fields-factory")),
	        array("id"=>"!=", "title"=>__("is not equal to", "wc-fields-factory"))
	    ));
	}
	
	public function search_posts($_search = '', $_post_type = 'product', $_parent = 0) {
	    global $wpdb;
		$res = array();		
		$posts = array();
		
		if (absint($_parent) != 0) {
		    $posts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='%s' AND post_parent=%d AND post_status='publish' AND post_excerpt LIKE '%s'", $_post_type, $_parent, '%'. $wpdb->esc_like($_search) .'%'));
		} else {
		    $posts = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title FROM $wpdb->posts WHERE post_type='%s' AND post_status='publish' AND post_title LIKE '%s'", $_post_type, '%'. $wpdb->esc_like($_search) .'%'));
		}		
	
		if ($_post_type != "product") {
		    foreach ($posts as $post) {
				$res[] = array(	"id" => $post->{"ID"}, "title" => $post->{"post_title"});
			}
		} else {
			$wcG3 = version_compare(WC()->version, '2.2.0', '<');
			foreach ($posts as $post) {
				$product = $wcG3 ? get_product($post->{"ID"}) : wc_get_product($post->{"ID"});
				if ($product->is_type('variable')) {
					$res[] = array(	"id" => $post->{"ID"}, "title" => $post->{"post_title"});
				}				
			}
		}		
		return $res;
	}
	
	public function save_map_wccvf_variations($_payload) {
		$res = false;		
		if (isset($_payload["rules"]) && isset($_payload["product"])) {
		    $variations = new WP_Query(array(
		        'post_type'      	=> 'product_variation',
		        'post_status'    	=> 'publish',
		        'post_parent'		=> absint($_payload["product"]),
		        'posts_per_page' 	=> -1,
		        'fields'         	=> 'ids'
		    ));	      
		    foreach ($_payload["rules"] as $fg => $rules) {		        
		        $current_rules = get_post_meta(absint($fg), 'wccvf_condition_rules', true);
		        $current_rules = json_decode($current_rules, TRUE);
		        if (is_array($current_rules)) {		            
		            foreach ($current_rules as $index => $crule) {
		                if (in_array($crule["endpoint"], $variations->posts)) {
		                    
		                    unset($current_rules[$index]);
		                }
		            }
		            
		        } else {
		            $current_rules = array();
		        }
		        
		        delete_post_meta($fg, $this->wcff_key_prefix .'condition_rules');
		        $res = add_post_meta($fg, $this->wcff_key_prefix .'condition_rules', json_encode(array_merge($current_rules, $rules)));
		    }
		}		
		return $res;
	}
	
	public function load_map_wccvf_variations() {
		
		$flag = false;
		$result = array();
		$vproducts = array();
		$all_rules = array();
		$wcG3 = version_compare(WC()->version, '2.2.0', '<');
		$posts = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));				
		$wccvfs = get_posts(array('post_type' => "wccvf", 'posts_per_page' => -1));
		
		foreach ($wccvfs as $wccvf) {
			$rules = get_post_meta($wccvf->ID, 'wccvf_condition_rules', true);
			$rules = json_decode($rules, true);
			if (is_array($rules)) {
				foreach ($rules as $rule) {
					$rule["group_id"] = $wccvf->ID;
					$rule["group_title"] = $wccvf->post_title;
					$all_rules[] = $rule;
				}
			}
		}
		
		foreach ($posts as $post) {
			$product = $wcG3 ? get_product($post->ID) : wc_get_product($post->ID);
			if ($product->is_type( 'variable' )) {
				$wp_query = new WP_Query(array(
					'post_type'      	=> 'product_variation',
					'post_status'    	=> 'publish',
					'post_parent'		=> $post->ID,
					'posts_per_page' 	=> -1,
					'fields'         	=> array('ID', 'post_title')
				));
				$vproducts[] = array("id" => $post->ID, "title" => $post->post_title, "variations" => $wp_query->posts);
			}
		}			
		
		foreach ($vproducts as $product) {
			if (is_array($product["variations"])) {
				$variations = array();
				foreach ($product["variations"] as $variation) {
					$flag = false;
					$fgroups = array();
					foreach ($all_rules as $rule) {
					    if (absint($rule["endpoint"]) == absint($variation->ID)) {
							$flag = true;
							$fgroups[] = array("gid" => $rule["group_id"], "gtitle" => $rule["group_title"]);
						}
					}
					if ($flag) {
					    $variations[$variation->ID] = array("variation_title" => $variation->post_excerpt, "groups" => $fgroups);						
					}
				}
				if (!empty($variations)) {
				    $result[$product["id"]] = array("product_title" => $product["title"], "variations" => $variations);
				}
			}			
		}
		
		return $result;
	}
		
	public function check_product_for_variation_mappings($_pid = 0, $_type) {
	    if ($_pid) {
	        $_pid = absint($_pid);
	        $all_rules = array();
	        $this->wcff_key_prefix = $_type . "_";	 
	        $posts = get_posts(array('post_type' => $_type, 'posts_per_page' => -1));
	        
	        foreach ($posts as $post) {
	            $rules = $this->load_target_products_rules($post->ID);	            
	            if (is_array($rules)) {
	                foreach ($rules as $rule) {	                    
	                    $all_rules[] = $rule;
	                }
	            }
	        }
	        
	        $wp_query = new WP_Query(array(
	            'post_type'      	=> 'product_variation',
	            'post_status'    	=> 'publish',
	            'post_parent'		=> $_pid,
	            'posts_per_page' 	=> -1,
	            'fields'         	=> array('ID', 'post_title')
	        ));
	        
	        foreach ($wp_query->posts as $variation) {	            
	            foreach ($all_rules as $rules) {
	                foreach ($rules as $rule) {
	                    if (absint($rule["endpoint"]) == absint($variation->ID)) {
	                        return true;
	                    }
	                }	                
	            }
	        }
	    }
	    return false;
	}
	
	public function remove_wccvf_mapping($_wccvf_id, $_endpoint) {
	    $res = false;
	    $rules = get_post_meta(absint($_wccvf_id), 'wccvf_condition_rules', true);
	    $rules = json_decode($rules, true);
	    if (is_array($rules)) {
	        foreach ($rules as $index => $rule) {
	            if(absint($rule["endpoint"]) == absint($_endpoint)) {
	                unset($rules[$index]);
	            }
	        }
	        delete_post_meta(absint($_wccvf_id), 'wccvf_condition_rules');
	        $res = add_post_meta(absint($_wccvf_id), 'wccvf_condition_rules', json_encode($rules));
	    }
	    return $res;
	}
	
	/**
	 *
	 * This function is used to load all wcff fields (actualy post meta) for a single WCFF post<br/>
	 * Mostly used in editing wccpf fields in admin screen
	 *
	 * @param 	integer	$pid	- WCFF Post Id
	 * @param  boolean	$sort   - Whether returning fields should be sorted
	 * @return 	array
	 *
	 */
	public function load_fields($_pid = 0, $_sort = true) {
		$fields = array();
		$_pid = absint($_pid);
		$meta = get_post_meta($_pid);
		
		$excluded_keys = $this->prepare_special_keys();	
		foreach ($meta as $key => $val) {		    
		    /* Exclude special purpose custom meta */
		    if (!in_array($key, $excluded_keys) && (strpos($key, $this->wcff_key_prefix) === 0)) {
		        $fields[$key] = json_decode($val[0], true);
		    }		   
		}
		
		if ($_sort) {
			$this->usort_by_column($fields, "order");
		}
		
		return apply_filters( $this->wcff_key_prefix .'fields', $fields, $_pid, $_sort );
	}
	
	/**
	 *
	 * Loads all fields of the given Fields Group Post
	 *
	 * @param number $_pid
	 * @param string $_mkey
	 * @return mixed
	 *
	 */
	public function load_field($_pid = 0, $_mkey = "") {
		$_pid = absint($_pid);
		$post = get_post($_pid);
		$field = get_post_meta($_pid, $_mkey, true);
		if ($field === "") {
		    $field = "{}";
		} 
		$field = json_decode($field, true);
		return apply_filters( $post->post_type .'_field', $field, $_pid, $_mkey );
	}
	
	/**
	 * 
	 * Create a Unique ID for the field and store with initial data
	 * 
	 * @param number $_pid
	 * @param string $_type
	 * @return string|boolean
	 */
	public function create_field($_pid = 0, $_type, $_order) {
		$_pid = absint($_pid);
		$id = $this->generate_unique_id();
		$meta = array (
			"id" => $id,
			"type" => $_type,
			"label" => "",
			"order" => $_order,
			"status" => true
		);		
		if (add_post_meta($_pid, ($this->wcff_key_prefix . $id), wp_slash(json_encode($meta)))) {
			return ($this->wcff_key_prefix . $id);
		}		
		return false;
	}
	
	/**
	 *
	 * Save the given field's config meta as the post meta on a given Fields Group Post.
	 *
	 * @param number $_pid
	 * @param object $_payload
	 * @return number|false
	 *
	 */
	public function save_field($_pid = 0, $_payload) {
		$_pid = absint($_pid);
		$_payload= apply_filters( 'wcff_before_save_'. $this->wcff_key_prefix .'_field', $_payload, $_pid );
		if (!isset($_payload["name"]) || $_payload["name"] == "_" || $_payload["name"] != "") {
			$_payload["key"] = $this->wcff_key_prefix . $this->url_slug($_payload["name"], array('delimiter' => '_'));
		}
		$flg = add_post_meta($_pid,  $_payload["key"], wp_slash(json_encode($_payload))) == false ? false : true;
		return $flg;
	}
	
	public function update_field($_pid, $_payload) {
		$msg = "";
		$res = true;
		$_pid = absint($_pid);
		if (isset($_payload["key"])) {
		    delete_post_meta($_pid, $_payload["key"]);
			if (add_post_meta($_pid,  $_payload["key"], wp_slash(json_encode($_payload))) == false) {
				$res = false;
				$msg = __( "Failed to update the custom field", "wc-fields-factory" );
			}
		}	
		return array("res" => $res, "msg" => $msg);
	}
	
	public function toggle_field($_pid, $_key, $_status) {
		$msg = "";
		$res = true;
		$meta_val = get_post_meta($_pid, $_key, true);
		if ($meta_val && !empty($meta_val)) {
			$field = json_decode($meta_val, true);
			if (isset($field["is_enable"])) {
				$field["is_enable"] = $_status;
				delete_post_meta($_pid, $_key);
				if (add_post_meta($_pid, $_key, wp_slash(json_encode($field))) == false) {
					$res = false;
					$msg = __( "Failed to update.!", "wc-fields-factory" );
				}
			} else {
				$res = false;
				$msg = __( "Failed to update, Key is missing.!", "wc-fields-factory" );
			}
		} else {
			$res = false;
			$msg = __( "Failed to update, Meta is empty.!", "wc-fields-factory" );
		}
		return array("res" => $res, "msg" => $msg);
	}
	
	public function clone_group($_pid = 0, $_post_type = "") {
		global $wpdb;		
		$_pid = ($_pid == 0) ? (isset($_REQUEST["post"]) ? $_REQUEST["post"] : 0) : 0;
		$_post_type = ($_post_type == "") ? (isset($_REQUEST["post_type"]) ? $_REQUEST["post_type"] : "") : "";
		if (isset($_pid) && $_pid > 0) {
			$post = get_post($_pid);
			$new_post_id = wp_insert_post(array(
				'comment_status' => $post->comment_status,
				'ping_status'    => $post->ping_status,
				'post_author'    => $post->post_author,
				'post_content'   => $post->post_content,
				'post_excerpt'   => $post->post_excerpt,
				'post_name'      => $post->post_name,
				'post_parent'    => $post->post_parent,
				'post_password'  => $post->post_password,
				'post_status'    => 'publish',
				'post_title'     => "Copy - ". $post->post_title,
				'post_type'      => $post->post_type,
				'to_ping'        => $post->to_ping,
				'menu_order'     => $post->menu_order
			));
			
			$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$_pid");
			if (count($post_meta_infos)!=0) {
				$sql_query_sel = array();
				$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
				foreach ($post_meta_infos as $meta_info) {
					$meta_key = $meta_info->meta_key;
					if( $meta_key == '_wp_old_slug' ) continue;
					$meta_value = addslashes($meta_info->meta_value);
					$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
				}
				$sql_query.= implode(" UNION ALL ", $sql_query_sel);
				$wpdb->query($sql_query);
			}
		}
		if ($_post_type != "wccvf") {
			wp_redirect( admin_url('edit.php?post_type='. $_post_type));
		} else {
			wp_redirect( admin_url('edit.php?post_type=wccpf&page=variation_fields_config'));
		}		
		exit;
	}
	
	public function clone_field($_pid, $_fkey) {
		$_pid = absint($_pid);
		$id = $this->generate_unique_id();
		$cloned = $this->load_field($_pid, $_fkey);		
		if (is_array($cloned)) {
			$cloned["id"] = $id;
			$cloned["label"] = "Copy - ". $cloned["label"];
			if (add_post_meta($_pid, ($this->wcff_key_prefix . $id), wp_slash(json_encode($cloned)))) {
				return ($this->wcff_key_prefix . $id);
			}
		}
		return false;
	}
	
	/**
	 *
	 * Update the given field's config meta as the post meta on a given Fields Group Post.
	 *
	 * @param number $_pid
	 * @param object $_payload
	 * @return number|boolean
	 *
	 */
	/* public function update_field($_pid = 0, $_payload) {
		$_pid = absint($_pid);
		$res  = true;
		$msg = "";
		$field_meta_key = "";
		$post = get_post($_pid);
		$field_unopen = isset($_payload["wcff_unopen_details"] ) ? $_payload["wcff_unopen_details"] : array();
		$_payload = isset($_payload["wcff_field_metas"]) ? $_payload["wcff_field_metas"] : array();
		for ($i = 0; $i < count($_payload); $i++) {
			$payload = $_payload[$i];
			if (isset($payload["key"]) && $payload["key"] != "") {
				$field_meta_key = $payload["key"];
			} else {
				$field_meta_key = "";
			}
			if ($res) {
				$post_meta = get_post_meta($_pid, $field_meta_key, true);
				$check_not_empty = !empty($field_meta_key) && !empty($post_meta);
				if ($check_not_empty) {
					$payload = apply_filters( 'wcff_before_update_'. $post->post_type .'_field', $_payload[$i], $_pid );
					delete_post_meta( $_pid, $field_meta_key );
					if (add_post_meta($_pid,  $field_meta_key, wp_slash(json_encode($payload))) == false) {
						$res = false;
						$msg = __( "Failed to update the custom field", "wc-fields-factory" );
					}
				} else {
					$res = $this->save_field($_pid, $_payload[$i]);
					if (!$res) {
						$msg = __( "Failed to create custom field", "wc-fields-factory" );
					}
				}
			}
		}
		
		foreach ($field_unopen as $key => $data) {
			$field_meta = get_post_meta($_pid,  $key, true);
			$check_empty = !empty($field_meta);
			if ($check_empty) {
				$field_meta_json = json_decode($field_meta, true);
				foreach ($data as $meta_key => $meta_val) {
					$field_meta_json[$meta_key] = $meta_val;
				}
				delete_post_meta($_pid, $key);
				if (add_post_meta($_pid, $key, wp_slash(json_encode($field_meta_json))) == false) {
					$res = false;
					$msg = __( "Failed to update the custom field", "wc-fields-factory" );
				}
			}
		}
		return array("res" => $res, "msg" => $msg);
	} */
	
	/**
	 *
	 * Remove the given field from Fields Group Post
	 *
	 * @param number $_pid
	 * @param string $_mkey
	 * @return boolean
	 *
	 */
	public function remove_field($_pid = 0, $_mkey) {
		$_pid = absint($_pid);
		$post = get_post($_pid);
		do_action($post->post_type .'_before_remove_field', $_mkey, $_pid);		
		/* Update the layout meta */
		$layout = $this->load_layout_meta($_pid);
		if (!empty($layout)) {
		    /* Row update */
		    foreach ($layout["rows"] as $rIndex => $row) {		        
		        foreach($row as $fIndex => $fkey) {		            
		            if ($_mkey == $fkey) {		                		                
		                if (count($row) == 1) {
		                    /* Could be only one field */
		                    unset($layout["rows"][$rIndex]); 
		                } else {
		                    $current_field_width = floatval($layout["columns"][$_mkey]["width"]);
		                    /* Could be first field */
		                    if ($fIndex == 0) {		                        
		                        $next_field_width = floatval($layout["columns"][$layout["rows"][$rIndex][$fIndex+1]]["width"]);
		                        $layout["columns"][$layout["rows"][$rIndex][$fIndex+1]]["width"] = ($current_field_width + $next_field_width);		                        
		                    } else {
		                        /* Could be last or middle */
		                        $prev_field_width = floatval($layout["columns"][$layout["rows"][$rIndex][$fIndex-1]]["width"]);
		                        $layout["columns"][$layout["rows"][$rIndex][$fIndex-1]]["width"] = ($current_field_width + $prev_field_width);
		                    }
		                    unset($layout["rows"][$rIndex][$fIndex]);
		                }		                
		            }
		        }
		    }
		    /* Column update */
		    unset($layout["columns"][$_mkey]);
		    
		    delete_post_meta($_pid, $this->wcff_key_prefix .'layout_meta');
		    add_post_meta($_pid, $this->wcff_key_prefix .'layout_meta', json_encode($layout));
		}
		
		return delete_post_meta($_pid, $_mkey);
	}
	
	/**
	 * 
	 * @param integer $_pid
	 * @param string $_type
	 * @param string $_template
	 * @param string $_fields_location
	 * 
	 */
	public function load_fields_groups_for_product($_pid, $_type = "wccpf", $_template = "single-product", $_fields_location = "") {
	    /* Holds custom post meta */
	    $meta = array();
	    /* Holds the fields list */
	    $fields = array();
	    /**/
	    $groups = array();
	    /* Holds the final list of fields */
	    $all_fields = array();
	    /* Location rules flag */
	    $location_passed = false;	
	    /* Condition rules flag */
	    $target_product_passed = false;
	        	    
	    $_pid = absint($_pid);
	    $this->wcff_key_prefix = $_type . "_";	    
	    $wcff_options = wcff()->option->get_options();
	    	    
	    /* Special keys that is not part of fields meta */
	    $excluded_keys = $this->prepare_special_keys();
	    
	    /* Fields on archive template Flaq */
	    $fields_on_archive = isset($wcff_options["fields_on_archive"]) ? $wcff_options["fields_on_archive"] : "no";
	    
	    /* Fields location on single product page */
	    $global_location_single = isset($wcff_options["field_location"]) ? $wcff_options["field_location"] : "woocommerce_before_add_to_cart_button";
	  
	    /* Fields location for archive product */
	    $global_location_archive = isset($wcff_options["field_archive_location"]) ? $wcff_options["field_archive_location"] : "woocommerce_before_shop_loop_item";
	    
	    /* Check whether the request for Archive template and fields on archive is enabled */
	    if ($_template == "archive-product" && $fields_on_archive == "no") {
	        /* No need to go further */
	        return apply_filters( 'wcff_fields_for_product', array(), $_pid, $_type, $_template, $_fields_location );
	    }
	    
	    /* Fetch the group posts */
	    $group_posts = get_posts(
	        array(
	            "post_type" => $_type, 
	            "posts_per_page" => -1,	
	            "order" => "ASC"	            
	        )
	    );	    
	    	    
	    if (count($group_posts) > 0) {
	        /* Loop through all group posts */
	        foreach ($group_posts as $g_post) {
	            
	            $all_fields = array();	            
	            /* Get all custom meta */
	            $fields = get_post_meta($g_post->ID);
	            
	            /* Check whether this group is for Authorized users only */
	            $authorized_only = get_post_meta($g_post->ID, $this->wcff_key_prefix."is_this_group_for_authorized_only", true);
	            $authorized_only = (!$authorized_only || $authorized_only == "") ? "no" : $authorized_only;
	            if ($authorized_only == "yes" && !is_user_logged_in()) {
	                continue;
	            }
	            
	            /* If it is for authorized only fields, then check for the roles */
	            if ($authorized_only == "yes" && !$this->check_for_roles($g_post)) {
	                continue;
	            }
	            	 
	            if ($_template != "any") {
	                /* Check for single-product location rule */
	                if ($_template == "single-product") {
	                    /* Group level Location */
	                    $field_group_location_single = get_post_meta($g_post->ID, $this->wcff_key_prefix."field_location_on_product", true);
	                    $field_group_location_single = empty($field_group_location_single) ? "use_global_setting" : $field_group_location_single;
	                    
	                    if ($field_group_location_single == "use_global_setting") {
	                        if ($_fields_location == "any" || $global_location_single == $_fields_location) {
	                            $location_passed = true;
	                        }	                        
	                    } else if ($_fields_location == "any" || $field_group_location_single == $_fields_location) {
	                        $location_passed = true;
	                    } else {
	                        /* Ignore */
	                    }	                    
	                } else if ($_template == "archive-product") {
	                   /* Check for archive-product location rule */	                
	                    $field_group_location_archive = get_post_meta($g_post->ID, $this->wcff_key_prefix."field_location_on_archive", true);
	                    $field_group_location_archive = empty( $field_group_location_archive ) ? "none" : $field_group_location_archive;
	                    
	                    if ($field_group_location_archive == "use_global_setting") {
	                        if ($_fields_location == "any" || $global_location_archive == $_fields_location) {
	                            $location_passed = true;
	                        }
	                    } else if ($_fields_location == "any" || $global_location_archive == $_fields_location) {
	                        $location_passed = true;
	                    } else {
	                        /* Ignore */
	                    }
	                }
	            } else {
	                $location_passed = true;
	            }
	            
	            /* Finally check for the target products */
	            $product_map_rules = $this->load_target_products_rules($g_post->ID);
	            
	            if (is_array($product_map_rules)) {
	                $target_product_passed = $this->check_for_product($_pid, $product_map_rules);
	            } else {
	                $target_product_passed = true;
	            }
	                    
	            if ($target_product_passed && $location_passed) {
	                /* Well prepare the field list */
	                foreach ($fields as $key => $meta) {
	                    /* Exclude special purpose custom meta */
	                    if (!in_array($key, $excluded_keys) && (strpos($key, $this->wcff_key_prefix) === 0)) {
	                        $field = json_decode($meta[0], true);	                        
	                        if ($_type == "wccaf" && ($_template == "single-product" || $_template == "archive-product")) {
	                            if ($field["show_on_product_page"] == "no") {
	                                continue;
	                            }	                            
	                        }
	                        if(isset($field["is_enable"])) {
	                            if ($field["is_enable"]) {
	                                $all_fields[] = $field;
	                            }	                            
	                        } else {
	                            $all_fields[] = $field;
	                        }
	                    }
	                }
	                $groups[] = array(
	                    "id" => $g_post->ID,
	                    "type" => $_type,
	                    "fields" => $all_fields,
	                    "title" =>  get_the_title($g_post->ID),
	                    "layout" => $this->load_layout_meta($g_post->ID),
	                    "use_custom_layout" => $this->load_use_custom_layout($g_post->ID),
	                    "show_title" => get_post_meta($g_post->ID, ($this->wcff_key_prefix ."show_group_title"), true),
	                    "is_clonable" => get_post_meta($g_post->ID, ($this->wcff_key_prefix ."is_this_group_clonable"), true),	                    
	                    "label_alignment" => get_post_meta($g_post->ID, ($this->wcff_key_prefix ."fields_label_alignement"), true),
	                    "template_single_location" => get_post_meta($g_post->ID, ($this->wcff_key_prefix ."field_location_on_product"), true),
                        "template_archive_location" => get_post_meta($g_post->ID, ($this->wcff_key_prefix ."field_location_on_archive"), true)
	                );
	            }
	            
	        }
	    }	
	    return apply_filters('wcff_fields_for_product', $groups, $_pid, $_type, $_template, $_fields_location);			    
	}
	
	
	/**
	 *
	 * WCFF Product Mapping Rules Engine, This is function used to determine whether or not to include<br/>
	 * a particular wccpf group fields to a particular Product
	 *
	 * @param 	integer		$_pid	- Product Id
	 * @param 	array 		$_groups
	 * @return 	boolean
	 *
	 */
	public function check_for_product($_pid, $_groups) {
		$matches = array();
		$final_matches = array();
		foreach ($_groups as $rules) {
			$ands = array();
			foreach ($rules as $rule) {	
			    /* Special case scenario only for Product Variations */
			    if ($rule["context" ] != "product_variation" && wcff()->request["context"] == "wcff_variation_fields") {
			        return false;
			    }
				if ($rule["context"] == "product") {
					if ($rule["endpoint"] == -1) {
						$ands[] = ($rule["logic"] == "==");
					} else {
						if ($rule["logic"] == "==") {
							$ands[] = ($_pid == $rule["endpoint"]);
						} else {
							$ands[] = ($_pid != $rule["endpoint"]);
						}
					}
				} else if ($rule["context"] == "product_variation") {
					if ($rule["endpoint"] == -1) {
						if (get_post_type($_pid) == "product_variation") {
							$ands[] = ($rule["logic"] == "==");
						} else {
							$ands[] = false;
						}
					} else {
						if ($rule["logic"] == "==") {
							if (get_post_type($_pid) == "product_variation") {
								$ands[] = ($_pid == $rule["endpoint"]);
							} else {
								$ands[] = false;
							}
						} else {
							if (get_post_type($_pid) == "product_variation") {
								$ands[] = ($_pid != $rule["endpoint"]);
							} else {
								$ands[] = false;
							}
						}
					}
				} else if ($rule["context"] == "product_cat") {
					if ($rule["endpoint"] == -1) {
						$ands[] = ($rule["logic"] == "==");
					} else {
						if ($rule["logic"] == "==") {
							$ands[] = has_term($rule["endpoint"], 'product_cat', $_pid);
						} else {
							$ands[] = !has_term($rule["endpoint"], 'product_cat', $_pid);
						}
					}
				}  else if ($rule["context"] == "product_tag") {
					if ($rule["endpoint"] == -1) {
						$ands[] = ($rule["logic"] == "==");
					} else {
						if ($rule["logic"] == "==") {
							$ands[] = has_term($rule["endpoint"], 'product_tag', $_pid);
						} else {
							$ands[] = !has_term($rule["endpoint"], 'product_tag', $_pid);
						}
					}
				}  else if ($rule["context"] == "product_type") {
					if ($rule["endpoint"] == -1) {
						$ands[] = ($rule["logic"] == "==");
					} else {
						$ptype = wp_get_object_terms($_pid, 'product_type');
						$ands[] = ($ptype[0]->slug == $rule["endpoint"]);
					}
				}
			}
			$matches[] = $ands;
		}		
		foreach ($matches as $match) {
			$final_matches[] = !in_array(false, $match);
		}		
		return in_array(true, $final_matches);
	}
	
	/**
	 *
	 * WCFF Location Rules Engine, This is function used to determine where does the  particular wccaf fields group<br/>
	 * to be placed. in the product view, product cat view or one of any product data sections ( Tabs )<br/>
	 * applicable only for wccaf post_type.
	 *
	 * @param integer $_pid
	 * @param array	$_groups
	 * @param string $_location
	 *
	 */
	public function check_for_location($_pid, $_groups, $_location, $product_cart_page = "product-page") {
		foreach ($_groups as $rules) {
			foreach ($rules as $rule) {				
				if ($rule["context"] == "location_product_data") {
					if ($rule["endpoint"] == $_location && $rule["logic"] == "==") {
						return true;
					}
				}
				if ($rule["context"] == "location_product" && $_location == "admin_head-post.php") {
					return true;
				}
				if ($rule["context"] == "location_product_cat" && ($_location == "product_cat_add_form_fields" || $_location == "product_cat_edit_form_fields"))  {
					return true;
				}
			}
		}		
		return false;
	}
	
	private function check_for_roles($_gpost) {	    
	    global $wp_roles;
	    $all_roles = array();
	    foreach ($wp_roles->roles as $handle => $role) {
	        $all_roles[] = $handle;
	    }
	    
	    $targeted_roles = get_post_meta($_gpost->ID, $_gpost->post_type ."_wcff_group_preference_target_roles", true);
	    if (!$targeted_roles || $targeted_roles == "") {
	        $targeted_roles = $all_roles;
	    } else {
	        $targeted_roles = json_decode($targeted_roles, true);
	    }
	    $user = wp_get_current_user();
	    
	    $intersect = array_intersect($targeted_roles, (array) $user->roles);
	    return (count($intersect) > 0);	    
	}
	
	/**
	 *
	 * Order the array for the given property.
	 *
	 * @param array $_arr
	 * @param string $_col
	 * @param string $_dir
	 *
	 */
	public function usort_by_column(&$_arr, $_col, $_dir = SORT_ASC) {
		$sort_col = array();
		foreach ($_arr as $key=> $row) {
			$sort_col[$key] = $row[$_col];
		}
		array_multisort($sort_col, $_dir, $_arr);
	}
	
	private function get_fields_count($_pid) {
		$count =0;
		$keys = get_post_custom_keys($_pid);		
		if ($keys) {
			foreach ($keys as $key) {
				if ((strpos($key, 'wccpf_') !== false ||
				strpos($key, 'wccaf_') !== false ||
				strpos($key, 'wccvf_') !== false) &&
				(strpos($key, 'group_rules') === false &&
						strpos($key, 'condition_rules') === false &&
						strpos($key, 'fee_rules') === false &&
						strpos($key, 'field_rules') === false &&
						strpos($key, 'location_rules') === false &&
						strpos($key, 'product_tab_title') === false &&
						strpos($key, 'product_tab_priority') === false &&
						strpos($key, 'field_location_on_product') === false &&
						strpos($key, 'field_location_on_archive') === false &&
						strpos($key, 'is_this_group_clonable') === false)) {
							$count++;
						}
			}
		}
		return $count;
	}
	
	/**
	 * 
	 * @return string
	 */
	private function generate_unique_id() {
		$token = '';
		$token_length = 12;
		$alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$alphabet .= "abcdefghijklmnopqrstuvwxyz";
		$alphabet .= "0123456789";
		$alphabetLength = strlen($alphabet);		
		for ($i = 0; $i < $token_length; $i++) {
			$randomKey = $this->get_random_number(0, $alphabetLength);
			$token .= $alphabet[$randomKey];
		}
		return $token;
	}
	
	/**
	 * 
	 * @param number $_min
	 * @param number $_max
	 * @return number
	 */
	private function get_random_number($_min, $_max) {
		$range = ($_max - $_min);
		if ($range < 0) {
			return $_min;
		}
		$log = log($range, 2);
		$bytes = (int) ($log / 8) + 1;
		$bits = (int) $log + 1;
		$filter = (int) (1 << $bits) - 1;
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter;
		} while ($rnd >= $range);
		return ($_min + $rnd);
	}
	
	private function prepare_special_keys() {
	    $excluded_keys = array();
	    if ($this->wcff_key_prefix != "") {
	        foreach ($this->special_keys as $key) {
	            $excluded_keys[] = $this->wcff_key_prefix . $key;
	        }
	    }
	    return $excluded_keys;
	}
	
	/**
	 *
	 * Create a web friendly URL slug from a string.
	 *
	 * @author Sean Murphy <sean@iamseanmurphy.com>
	 * @copyright Copyright 2012 Sean Murphy. All rights reserved.
	 * @license http://creativecommons.org/publicdomain/zero/1.0/
	 *
	 * @param string $str
	 * @param array $options
	 * @return string
	 *
	 */
	function url_slug($_str, $_options = array()) {		
		// Make sure string is in UTF-8 and strip invalid UTF-8 characters
		$_str = mb_convert_encoding((string) $_str, 'UTF-8', mb_list_encodings());
		
		$defaults = array (
			'delimiter' => '-',
			'limit' => null,
			'lowercase' => true,
			'replacements' => array(),
			'transliterate' => false,
		);
		
		// Merge options
		$_options = array_merge($defaults, $_options);
		
		$char_map = array (
			// Latin
			'ÃƒÆ’Ã¢â€šÂ¬' => 'A', 'ÃƒÆ’Ã¯Â¿Â½' => 'A', 'ÃƒÆ’Ã¢â‚¬Å¡' => 'A', 'ÃƒÆ’Ã†â€™' => 'A', 'ÃƒÆ’Ã¢â‚¬Å¾' => 'A', 'ÃƒÆ’Ã¢â‚¬Â¦' => 'A', 'ÃƒÆ’Ã¢â‚¬Â ' => 'AE', 'ÃƒÆ’Ã¢â‚¬Â¡' => 'C',
			'ÃƒÆ’Ã‹â€ ' => 'E', 'ÃƒÆ’Ã¢â‚¬Â°' => 'E', 'ÃƒÆ’Ã…Â ' => 'E', 'ÃƒÆ’Ã¢â‚¬Â¹' => 'E', 'ÃƒÆ’Ã…â€™' => 'I', 'ÃƒÆ’Ã¯Â¿Â½' => 'I', 'ÃƒÆ’Ã…Â½' => 'I', 'ÃƒÆ’Ã¯Â¿Â½' => 'I',
			'ÃƒÆ’Ã¯Â¿Â½' => 'D', 'ÃƒÆ’Ã¢â‚¬Ëœ' => 'N', 'ÃƒÆ’Ã¢â‚¬â„¢' => 'O', 'ÃƒÆ’Ã¢â‚¬Å“' => 'O', 'ÃƒÆ’Ã¢â‚¬ï¿½' => 'O', 'ÃƒÆ’Ã¢â‚¬Â¢' => 'O', 'ÃƒÆ’Ã¢â‚¬â€œ' => 'O', 'Ãƒâ€¦Ã¯Â¿Â½' => 'O',
			'ÃƒÆ’Ã‹Å“' => 'O', 'ÃƒÆ’Ã¢â€žÂ¢' => 'U', 'ÃƒÆ’Ã…Â¡' => 'U', 'ÃƒÆ’Ã¢â‚¬Âº' => 'U', 'ÃƒÆ’Ã…â€œ' => 'U', 'Ãƒâ€¦Ã‚Â°' => 'U', 'ÃƒÆ’Ã¯Â¿Â½' => 'Y', 'ÃƒÆ’Ã…Â¾' => 'TH',
			'ÃƒÆ’Ã…Â¸' => 'ss',
			'ÃƒÆ’Ã‚Â ' => 'a', 'ÃƒÆ’Ã‚Â¡' => 'a', 'ÃƒÆ’Ã‚Â¢' => 'a', 'ÃƒÆ’Ã‚Â£' => 'a', 'ÃƒÆ’Ã‚Â¤' => 'a', 'ÃƒÆ’Ã‚Â¥' => 'a', 'ÃƒÆ’Ã‚Â¦' => 'ae', 'ÃƒÆ’Ã‚Â§' => 'c',
			'ÃƒÆ’Ã‚Â¨' => 'e', 'ÃƒÆ’Ã‚Â©' => 'e', 'ÃƒÆ’Ã‚Âª' => 'e', 'ÃƒÆ’Ã‚Â«' => 'e', 'ÃƒÆ’Ã‚Â¬' => 'i', 'ÃƒÆ’Ã‚Â­' => 'i', 'ÃƒÆ’Ã‚Â®' => 'i', 'ÃƒÆ’Ã‚Â¯' => 'i',
			'ÃƒÆ’Ã‚Â°' => 'd', 'ÃƒÆ’Ã‚Â±' => 'n', 'ÃƒÆ’Ã‚Â²' => 'o', 'ÃƒÆ’Ã‚Â³' => 'o', 'ÃƒÆ’Ã‚Â´' => 'o', 'ÃƒÆ’Ã‚Âµ' => 'o', 'ÃƒÆ’Ã‚Â¶' => 'o', 'Ãƒâ€¦Ã¢â‚¬Ëœ' => 'o',
			'ÃƒÆ’Ã‚Â¸' => 'o', 'ÃƒÆ’Ã‚Â¹' => 'u', 'ÃƒÆ’Ã‚Âº' => 'u', 'ÃƒÆ’Ã‚Â»' => 'u', 'ÃƒÆ’Ã‚Â¼' => 'u', 'Ãƒâ€¦Ã‚Â±' => 'u', 'ÃƒÆ’Ã‚Â½' => 'y', 'ÃƒÆ’Ã‚Â¾' => 'th',
			'ÃƒÆ’Ã‚Â¿' => 'y',
			// Latin symbols
			'Ãƒâ€šÃ‚Â©' => '(c)',
			// Greek
			'ÃƒÅ½Ã¢â‚¬Ëœ' => 'A', 'ÃƒÅ½Ã¢â‚¬â„¢' => 'B', 'ÃƒÅ½Ã¢â‚¬Å“' => 'G', 'ÃƒÅ½Ã¢â‚¬ï¿½' => 'D', 'ÃƒÅ½Ã¢â‚¬Â¢' => 'E', 'ÃƒÅ½Ã¢â‚¬â€œ' => 'Z', 'ÃƒÅ½Ã¢â‚¬â€�' => 'H', 'ÃƒÅ½Ã‹Å“' => '8',
			'ÃƒÅ½Ã¢â€žÂ¢' => 'I', 'ÃƒÅ½Ã…Â¡' => 'K', 'ÃƒÅ½Ã¢â‚¬Âº' => 'L', 'ÃƒÅ½Ã…â€œ' => 'M', 'ÃƒÅ½Ã¯Â¿Â½' => 'N', 'ÃƒÅ½Ã…Â¾' => '3', 'ÃƒÅ½Ã…Â¸' => 'O', 'ÃƒÅ½Ã‚Â ' => 'P',
			'ÃƒÅ½Ã‚Â¡' => 'R', 'ÃƒÅ½Ã‚Â£' => 'S', 'ÃƒÅ½Ã‚Â¤' => 'T', 'ÃƒÅ½Ã‚Â¥' => 'Y', 'ÃƒÅ½Ã‚Â¦' => 'F', 'ÃƒÅ½Ã‚Â§' => 'X', 'ÃƒÅ½Ã‚Â¨' => 'PS', 'ÃƒÅ½Ã‚Â©' => 'W',
			'ÃƒÅ½Ã¢â‚¬Â ' => 'A', 'ÃƒÅ½Ã‹â€ ' => 'E', 'ÃƒÅ½Ã…Â ' => 'I', 'ÃƒÅ½Ã…â€™' => 'O', 'ÃƒÅ½Ã…Â½' => 'Y', 'ÃƒÅ½Ã¢â‚¬Â°' => 'H', 'ÃƒÅ½Ã¯Â¿Â½' => 'W', 'ÃƒÅ½Ã‚Âª' => 'I',
			'ÃƒÅ½Ã‚Â«' => 'Y',
			'ÃƒÅ½Ã‚Â±' => 'a', 'ÃƒÅ½Ã‚Â²' => 'b', 'ÃƒÅ½Ã‚Â³' => 'g', 'ÃƒÅ½Ã‚Â´' => 'd', 'ÃƒÅ½Ã‚Âµ' => 'e', 'ÃƒÅ½Ã‚Â¶' => 'z', 'ÃƒÅ½Ã‚Â·' => 'h', 'ÃƒÅ½Ã‚Â¸' => '8',
			'ÃƒÅ½Ã‚Â¹' => 'i', 'ÃƒÅ½Ã‚Âº' => 'k', 'ÃƒÅ½Ã‚Â»' => 'l', 'ÃƒÅ½Ã‚Â¼' => 'm', 'ÃƒÅ½Ã‚Â½' => 'n', 'ÃƒÅ½Ã‚Â¾' => '3', 'ÃƒÅ½Ã‚Â¿' => 'o', 'Ãƒï¿½Ã¢â€šÂ¬' => 'p',
			'Ãƒï¿½Ã¯Â¿Â½' => 'r', 'Ãƒï¿½Ã†â€™' => 's', 'Ãƒï¿½Ã¢â‚¬Å¾' => 't', 'Ãƒï¿½Ã¢â‚¬Â¦' => 'y', 'Ãƒï¿½Ã¢â‚¬Â ' => 'f', 'Ãƒï¿½Ã¢â‚¬Â¡' => 'x', 'Ãƒï¿½Ã‹â€ ' => 'ps', 'Ãƒï¿½Ã¢â‚¬Â°' => 'w',
			'ÃƒÅ½Ã‚Â¬' => 'a', 'ÃƒÅ½Ã‚Â­' => 'e', 'ÃƒÅ½Ã‚Â¯' => 'i', 'Ãƒï¿½Ã…â€™' => 'o', 'Ãƒï¿½Ã¯Â¿Â½' => 'y', 'ÃƒÅ½Ã‚Â®' => 'h', 'Ãƒï¿½Ã…Â½' => 'w', 'Ãƒï¿½Ã¢â‚¬Å¡' => 's',
			'Ãƒï¿½Ã…Â ' => 'i', 'ÃƒÅ½Ã‚Â°' => 'y', 'Ãƒï¿½Ã¢â‚¬Â¹' => 'y', 'ÃƒÅ½Ã¯Â¿Â½' => 'i',
			// Turkish
			'Ãƒâ€¦Ã…Â¾' => 'S', 'Ãƒâ€žÃ‚Â°' => 'I', 'ÃƒÆ’Ã¢â‚¬Â¡' => 'C', 'ÃƒÆ’Ã…â€œ' => 'U', 'ÃƒÆ’Ã¢â‚¬â€œ' => 'O', 'Ãƒâ€žÃ…Â¾' => 'G',
			'Ãƒâ€¦Ã…Â¸' => 's', 'Ãƒâ€žÃ‚Â±' => 'i', 'ÃƒÆ’Ã‚Â§' => 'c', 'ÃƒÆ’Ã‚Â¼' => 'u', 'ÃƒÆ’Ã‚Â¶' => 'o', 'Ãƒâ€žÃ…Â¸' => 'g',
			// Russian
			'Ãƒï¿½Ã¯Â¿Â½' => 'A', 'Ãƒï¿½Ã¢â‚¬Ëœ' => 'B', 'Ãƒï¿½Ã¢â‚¬â„¢' => 'V', 'Ãƒï¿½Ã¢â‚¬Å“' => 'G', 'Ãƒï¿½Ã¢â‚¬ï¿½' => 'D', 'Ãƒï¿½Ã¢â‚¬Â¢' => 'E', 'Ãƒï¿½Ã¯Â¿Â½' => 'Yo', 'Ãƒï¿½Ã¢â‚¬â€œ' => 'Zh',
			'Ãƒï¿½Ã¢â‚¬â€�' => 'Z', 'Ãƒï¿½Ã‹Å“' => 'I', 'Ãƒï¿½Ã¢â€žÂ¢' => 'J', 'Ãƒï¿½Ã…Â¡' => 'K', 'Ãƒï¿½Ã¢â‚¬Âº' => 'L', 'Ãƒï¿½Ã…â€œ' => 'M', 'Ãƒï¿½Ã¯Â¿Â½' => 'N', 'Ãƒï¿½Ã…Â¾' => 'O',
			'Ãƒï¿½Ã…Â¸' => 'P', 'Ãƒï¿½Ã‚Â ' => 'R', 'Ãƒï¿½Ã‚Â¡' => 'S', 'Ãƒï¿½Ã‚Â¢' => 'T', 'Ãƒï¿½Ã‚Â£' => 'U', 'Ãƒï¿½Ã‚Â¤' => 'F', 'Ãƒï¿½Ã‚Â¥' => 'H', 'Ãƒï¿½Ã‚Â¦' => 'C',
			'Ãƒï¿½Ã‚Â§' => 'Ch', 'Ãƒï¿½Ã‚Â¨' => 'Sh', 'Ãƒï¿½Ã‚Â©' => 'Sh', 'Ãƒï¿½Ã‚Âª' => '', 'Ãƒï¿½Ã‚Â«' => 'Y', 'Ãƒï¿½Ã‚Â¬' => '', 'Ãƒï¿½Ã‚Â­' => 'E', 'Ãƒï¿½Ã‚Â®' => 'Yu',
			'Ãƒï¿½Ã‚Â¯' => 'Ya',
			'Ãƒï¿½Ã‚Â°' => 'a', 'Ãƒï¿½Ã‚Â±' => 'b', 'Ãƒï¿½Ã‚Â²' => 'v', 'Ãƒï¿½Ã‚Â³' => 'g', 'Ãƒï¿½Ã‚Â´' => 'd', 'Ãƒï¿½Ã‚Âµ' => 'e', 'Ãƒâ€˜Ã¢â‚¬Ëœ' => 'yo', 'Ãƒï¿½Ã‚Â¶' => 'zh',
			'Ãƒï¿½Ã‚Â·' => 'z', 'Ãƒï¿½Ã‚Â¸' => 'i', 'Ãƒï¿½Ã‚Â¹' => 'j', 'Ãƒï¿½Ã‚Âº' => 'k', 'Ãƒï¿½Ã‚Â»' => 'l', 'Ãƒï¿½Ã‚Â¼' => 'm', 'Ãƒï¿½Ã‚Â½' => 'n', 'Ãƒï¿½Ã‚Â¾' => 'o',
			'Ãƒï¿½Ã‚Â¿' => 'p', 'Ãƒâ€˜Ã¢â€šÂ¬' => 'r', 'Ãƒâ€˜Ã¯Â¿Â½' => 's', 'Ãƒâ€˜Ã¢â‚¬Å¡' => 't', 'Ãƒâ€˜Ã†â€™' => 'u', 'Ãƒâ€˜Ã¢â‚¬Å¾' => 'f', 'Ãƒâ€˜Ã¢â‚¬Â¦' => 'h', 'Ãƒâ€˜Ã¢â‚¬Â ' => 'c',
			'Ãƒâ€˜Ã¢â‚¬Â¡' => 'ch', 'Ãƒâ€˜Ã‹â€ ' => 'sh', 'Ãƒâ€˜Ã¢â‚¬Â°' => 'sh', 'Ãƒâ€˜Ã…Â ' => '', 'Ãƒâ€˜Ã¢â‚¬Â¹' => 'y', 'Ãƒâ€˜Ã…â€™' => '', 'Ãƒâ€˜Ã¯Â¿Â½' => 'e', 'Ãƒâ€˜Ã…Â½' => 'yu',
			'Ãƒâ€˜Ã¯Â¿Â½' => 'ya',
			// Ukrainian
			'Ãƒï¿½Ã¢â‚¬Å¾' => 'Ye', 'Ãƒï¿½Ã¢â‚¬Â ' => 'I', 'Ãƒï¿½Ã¢â‚¬Â¡' => 'Yi', 'Ãƒâ€™Ã¯Â¿Â½' => 'G',
			'Ãƒâ€˜Ã¢â‚¬ï¿½' => 'ye', 'Ãƒâ€˜Ã¢â‚¬â€œ' => 'i', 'Ãƒâ€˜Ã¢â‚¬â€�' => 'yi', 'Ãƒâ€™Ã¢â‚¬Ëœ' => 'g',
			// Czech
			'Ãƒâ€žÃ…â€™' => 'C', 'Ãƒâ€žÃ…Â½' => 'D', 'Ãƒâ€žÃ…Â¡' => 'E', 'Ãƒâ€¦Ã¢â‚¬Â¡' => 'N', 'Ãƒâ€¦Ã‹Å“' => 'R', 'Ãƒâ€¦Ã‚Â ' => 'S', 'Ãƒâ€¦Ã‚Â¤' => 'T', 'Ãƒâ€¦Ã‚Â®' => 'U',
			'Ãƒâ€¦Ã‚Â½' => 'Z',
			'Ãƒâ€žÃ¯Â¿Â½' => 'c', 'Ãƒâ€žÃ¯Â¿Â½' => 'd', 'Ãƒâ€žÃ¢â‚¬Âº' => 'e', 'Ãƒâ€¦Ã‹â€ ' => 'n', 'Ãƒâ€¦Ã¢â€žÂ¢' => 'r', 'Ãƒâ€¦Ã‚Â¡' => 's', 'Ãƒâ€¦Ã‚Â¥' => 't', 'Ãƒâ€¦Ã‚Â¯' => 'u',
			'Ãƒâ€¦Ã‚Â¾' => 'z',
			// Polish
			'Ãƒâ€žÃ¢â‚¬Å¾' => 'A', 'Ãƒâ€žÃ¢â‚¬Â ' => 'C', 'Ãƒâ€žÃ‹Å“' => 'e', 'Ãƒâ€¦Ã¯Â¿Â½' => 'L', 'Ãƒâ€¦Ã†â€™' => 'N', 'ÃƒÆ’Ã¢â‚¬Å“' => 'o', 'Ãƒâ€¦Ã…Â¡' => 'S', 'Ãƒâ€¦Ã‚Â¹' => 'Z',
			'Ãƒâ€¦Ã‚Â»' => 'Z',
			'Ãƒâ€žÃ¢â‚¬Â¦' => 'a', 'Ãƒâ€žÃ¢â‚¬Â¡' => 'c', 'Ãƒâ€žÃ¢â€žÂ¢' => 'e', 'Ãƒâ€¦Ã¢â‚¬Å¡' => 'l', 'Ãƒâ€¦Ã¢â‚¬Å¾' => 'n', 'ÃƒÆ’Ã‚Â³' => 'o', 'Ãƒâ€¦Ã¢â‚¬Âº' => 's', 'Ãƒâ€¦Ã‚Âº' => 'z',
			'Ãƒâ€¦Ã‚Â¼' => 'z',
			// Latvian
			'Ãƒâ€žÃ¢â€šÂ¬' => 'A', 'Ãƒâ€žÃ…â€™' => 'C', 'Ãƒâ€žÃ¢â‚¬â„¢' => 'E', 'Ãƒâ€žÃ‚Â¢' => 'G', 'Ãƒâ€žÃ‚Âª' => 'i', 'Ãƒâ€žÃ‚Â¶' => 'k', 'Ãƒâ€žÃ‚Â»' => 'L', 'Ãƒâ€¦Ã¢â‚¬Â¦' => 'N',
			'Ãƒâ€¦Ã‚Â ' => 'S', 'Ãƒâ€¦Ã‚Âª' => 'u', 'Ãƒâ€¦Ã‚Â½' => 'Z',
			'Ãƒâ€žÃ¯Â¿Â½' => 'a', 'Ãƒâ€žÃ¯Â¿Â½' => 'c', 'Ãƒâ€žÃ¢â‚¬Å“' => 'e', 'Ãƒâ€žÃ‚Â£' => 'g', 'Ãƒâ€žÃ‚Â«' => 'i', 'Ãƒâ€žÃ‚Â·' => 'k', 'Ãƒâ€žÃ‚Â¼' => 'l', 'Ãƒâ€¦Ã¢â‚¬Â ' => 'n',
			'Ãƒâ€¦Ã‚Â¡' => 's', 'Ãƒâ€¦Ã‚Â«' => 'u', 'Ãƒâ€¦Ã‚Â¾' => 'z'
		);
		
		// Make custom replacements
		$_str = preg_replace(array_keys($_options['replacements']), $_options['replacements'], $_str);
		
		// Transliterate characters to ASCII
		if ($_options['transliterate']) {
			$_str = str_replace(array_keys($char_map), $char_map, $_str);
		}
		
		// Replace non-alphanumeric characters with our delimiter
		$_str = preg_replace('/[^\p{L}\p{Nd}]+/u', $_options['delimiter'], $_str);
		
		// Remove duplicate delimiters
		$_str = preg_replace('/('. preg_quote($_options['delimiter'], '/') .'){2,}/', '$1', $_str);
		
		// Truncate slug to max. characters
		$_str= mb_substr($_str, 0, ($_options['limit'] ? $_options['limit'] : mb_strlen($_str, 'UTF-8')), 'UTF-8');
		
		// Remove delimiter from ends
		$_str = trim($_str, $_options['delimiter']);
		
		return $_options['lowercase'] ? mb_strtolower($_str, 'UTF-8') : $_str;		
	}

}

?>