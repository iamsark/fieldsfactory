<?php 

if (!defined('ABSPATH')) { exit; }

/**
 * 
 * @author 	    : Saravana Kumar K
 * @copyright 	: Sarkware Research & Development (OPC) Pvt Ltd
 *
 */

class wcff_injector {
	
	/* Current product object 
	 * The product that is being viewed by the user */
	private $product = null;
	/* Fields group index property */
	private $group_index = 1;
	/* Global cloning property */
	private $is_cloning_enabled = "no";
	/* Multilingual property */
	private $is_multilingual_enabled = "no";
	/* Holds the Product Field Groups list */
	private $product_field_groups = null;
	/* Holds the Admin Field Groups list */
	private $admin_field_groups = null;
	/* Total color picker instance count */
	private $color_picker_count = 0;	
	/* Used to determine whether the cloning hidden count field injected or not */
	private $cloning_helper_input_inserted = false;
	/* Holds the meta list of all the date fields that is being injected */
	private $date_fields = array();
	/* Holds the meta list of all the color fields that is being injected */
	private $color_fields = array();	
	/* Holds the field rules list of all the fields that is being injected */
	private $fields_rules = array();
	/* Holds the pricing rules list of all the fields that is being injected */
	private $pricing_rules = array();
		
	/* Default constructor */
	public function __construct() {}
	
	public function inject_product_fields($_location, $_template = 'single-product') {
		
		Global $product;
		$this->product = $product;
		$cloning_title = "";
		$product_id = $this->get_product_id($this->product);
		$wcff_options = wcff()->option->get_options();

		$this->is_cloning_enabled = isset($wcff_options["fields_cloning"]) ? $wcff_options["fields_cloning"] : "no";
		$this->is_multilingual_enabled = isset($wcff_options["enable_multilingual"]) ? $wcff_options["enable_multilingual"] : "no";
		
		if (isset($wcff_options["global_cloning_title"]) && $wcff_options["global_cloning_title"] != "") {
		    $cloning_title = $wcff_options["global_cloning_title"];
		} else {
		    $cloning_title = "Additional Options";
		}
		
		/* Translate cloning title - if multilingual option enabled */
		if ($this->is_multilingual_enabled == "yes") {
			$current_locale = wcff()->locale->detrmine_current_locale();
			if ($current_locale != "en" && isset($wcff_options["global_cloning_title_". $current_locale]) && ! empty($wcff_options["global_cloning_title_". $current_locale])) {
			    $cloning_title = $wcff_options["global_cloning_title_". $current_locale];
			}
		}
		
		/* Let other plugins change the Cloning Title */
		if (has_filter('wcff_cloning_fields_group_title')) {
		    $cloning_title = apply_filters('wcff_cloning_fields_group_title', $cloning_title);
		}
		
		$this->product_field_groups = wcff()->dao->load_fields_groups_for_product($product_id, 'wccpf', $_template, $_location);
		$this->admin_field_groups = wcff()->dao->load_fields_groups_for_product($product_id, 'wccaf', $_template, $_location);
		
		do_action('wccpf_before_render_start', $_location, $_template);
		
		/* Inject label field - whichever comes at top */
		$this->handle_label_field("beginning");
		
		if ($this->has_any_fields_to_render($this->product_field_groups)) {		  
		    $this->fields_render_loop($this->product_field_groups, $_location, $cloning_title);		    
		}	
		
		if ($this->has_any_fields_to_render($this->admin_field_groups)) {
		    $this->fields_render_loop($this->admin_field_groups, $_location, $cloning_title);
		}		
		
		/* Inject label field - whichever comes at top */
		$this->handle_label_field("end");
		
		do_action('wccpf_after_render_end', $_location, $_template);	
		
		/* Store the template in session, used later in validation */
		WC()->session->set("wcff_current_template", $_template);
	}
	
	public function inject_placeholder_for_variation_fields() {
	    Global $product;
	    $product_id = $this->get_product_id($product);
	    if ($product->is_type('variable')) {
	        $wccvf_flag = wcff()->dao->check_product_for_variation_mappings($product_id, "wccvf");
	        $wccpf_flag = wcff()->dao->check_product_for_variation_mappings($product_id, "wccpf");	     
	        if ($wccvf_flag || $wccpf_flag) {
	            echo '<div class="wcff-variation-fields" data-area="'. current_action() .'"></div>';
	        }
	    }
	}
	
	/**
	 * 
	 * @param integer $_variation_id
	 * @return string
	 * 
	 */
	public function inject_variation_fields($_variation_id) {
	    
	    $html = '';
	    $cloning_title = '';
	    $wcff_options = wcff()->option->get_options();
	    
	    if (isset($wcff_options["global_cloning_title"]) && $wcff_options["global_cloning_title"] != "") {
	        $cloning_title = $wcff_options["global_cloning_title"];
	    } else {
	        $cloning_title = "Additional Options";
	    }
	    
	    $this->is_cloning_enabled = isset( $wcff_options["fields_cloning"] ) ? $wcff_options["fields_cloning"] : "no";
	    $this->is_multilingual_enabled = isset($wcff_options["enable_multilingual"]) ? $wcff_options["enable_multilingual"] : "no";
	    
	    $wccpf_posts = wcff()->dao->load_fields_groups_for_product($_variation_id, 'wccpf', "any");
	    $wccaf_posts = wcff()->dao->load_fields_groups_for_product($_variation_id, 'wccaf', "any");
	    $wccvf_posts = wcff()->dao->load_fields_groups_for_product($_variation_id, 'wccvf', "any");
	    
	    if ($this->has_any_fields_to_render($wccpf_posts)) {	
	        $html .= $this->fields_render_loop($wccpf_posts, "any", $cloning_title, false);
	    }
	    
	    if ($this->has_any_fields_to_render($wccaf_posts)) {
	        $html .= $this->fields_render_loop($wccaf_posts, "any", $cloning_title, false);
	    }
	    
	    if ($this->has_any_fields_to_render($wccvf_posts)) {
	        $html .= $this->fields_render_loop($wccvf_posts, "any", $cloning_title, false);
	    }
	    
	    return array(
	        "html" => $html,
	        "meta" => $this->enqueue_wcff_client_side_meta(false)
	    );	    
	    
	}
	
	/**
	 * 
	 * @param array $_groups
	 * @param string $_location
	 * @param string $cloning_title
	 * @param boolean $_echo
	 * @return string
	 * 
	 */
	private function fields_render_loop($_groups, $_location, $cloning_title, $_echo = true) {

	    /* Start of the global container */
	    $html = '<div class="wccpf-fields-container '. $_location .'">';
	    
	    foreach ($_groups as $group) {
	        if (count($group["fields"]) > 0) {
	            
	            do_action('wccpf_before_group_render_start', $_location, $group);
	            
	            /* Start of the group wrapper */
	            $html .= '<div class="wccpf-fields-group-container">';
	            
	            /* Check for the cloning */
	            if ($this->is_cloning_enabled == "yes" && $group["is_clonable"] == "yes") {
	                /* Start of the cloning container */
	                $html .= '<div class="wccpf-fields-group-clone-container">';
	            }
	            
	            $show_group_index = apply_filters("wccpf_display_group_index_on_cloning", true);
	            
	            /* Check for the group title */
	            if ($group["show_title"] == "yes") {
	                $html .= '<h4 class="wccpf-group-title-h4">'. $group["title"];
	                if ($this->is_cloning_enabled == "yes" && $group["is_clonable"] == "yes") {	                    
	                    if ($show_group_index) {
	                        $html .= ' <span class="wccpf-fields-group-title-index">1</span>';
	                    }	                    
	                }
	                $html .= '</h4>';
	            } else {
	                if ($this->is_cloning_enabled == "yes" && $group["is_clonable"] == "yes") {
	                    $html .= '<h4 class="wccpf-group-title-h4">'. $cloning_title;
	                    if ($show_group_index) {
	                        $html .= ' <span class="wccpf-fields-group-title-index">1</span>';
	                    }	
	                    $html .= '</h4>';	                   
	                }
	            }
	            
	            /* Inject the fields */
	            if ($group["use_custom_layout"] == "no") {
	                $html .= $this->render_product_fields($group);
	            } else {
	                $html .= $this->render_product_fields_with_custom_layout($group);
	            }
	            
	            if ($this->is_cloning_enabled == "yes" && $group["is_clonable"] == "yes") {
	                /* End of cloning container */
	                $html .= '</div>';
	            }
	            
	            /* End of the group wrapper */
	            $html .= '</div>';
	            
	            do_action('wccpf_after_group_render_end', $_location, $group);
	        }
	    }
	    
	    if ($this->is_cloning_enabled == "yes" && !$this->cloning_helper_input_inserted) {
	        $html .= '<input type="hidden" id="wccpf_fields_clone_count" value="1" />';
	        $this->cloning_helper_input_inserted = true;
	    }
	    
	    /* End of the global container */	   
	    $html .= '</div>';
	    
	    if ($_echo) {
	        echo $html;
	    } else {
	        return $html;
	    }
	}
	
	/**
	 * 
	 * @param array $_group
	 * @return string
	 * 
	 */
	private function render_product_fields($_group) {
	    $pHtml = "";	    
	    if (count($_group["fields"]) > 0) {	        
	        $pHtml = '<div class="wcff-fields-group" data-custom-layout="'. $_group["use_custom_layout"] .'" data-group-clonable="'. $_group["is_clonable"] .'">';	        
	        foreach ($_group["fields"] as $field) {                
                if (!isset( $field["type"] )){
                    continue;
                }
                if ($field["type"] == "label" && $field["position"] != "normal") {
                    continue;
                }
                if ($this->multilingual == "yes") {
                    /* Localize field */
                    //$field = wcff()->locale->localize_field($field);
                }
                /*
                 * This is not necessary here, but variation fields have some issues, so we have to do this in all places
                 * Since CSS class name connot contains special characters especially [ ] */
                if ($field["type"] == "datepicker" || $field["type"] == "colorpicker") {
                    $field["admin_class"] = $field["key"];
                    if ($field["type"] == "colorpicker") {                       
                        $this->color_fields[] = $field;                
                    }
                    if ($field["type"] == "datepicker") {
                        $this->date_fields[] = $field;
                    }
                }
                
                if (WC()->session->__isset("wcff_validation_failed")) {
                    /* Last add to cart operation failed
                     * Try to restore the fields old value */
                    $index = "";
                    if ($this->is_cloning_enabled == "yes" && $_group["is_clonable"] == "yes") {
                        $index= "_1";
                    }
                    if (isset($_REQUEST[$field["key"] . $index])) {
                        $field["default_value"] = $_REQUEST[$field["key"] . $index];
                    }
                }
                
                /* Inject label alignment property */
                $field["label_alignment"] = isset($_group["label_alignment"]) ? $_group["label_alignment"] : "left";
                
                /* Collecting Field rules meta */
                if (isset($field["field_rules"]) && is_array($field["field_rules"]) && count($field["field_rules"]) != 0) {
                    $this->fields_rules[$field["key"]] = $field["field_rules"];                    
                }
                
                /* Collecting Pricing rules meta */
                if (isset($field["pricing_rules"]) && is_array($field["pricing_rules"]) && count($field["pricing_rules"]) != 0) {
                    $this->pricing_rules[$field["key"]] = $field["pricing_rules"]; 
                }
                
                /* generate html for wccpf fields */
                $html = wcff()->builder->build_user_field($field, "wccpf", $this->is_cloning_enabled, $_group["is_clonable"]);
                                
                /* Allow third party apps logic to render wccpf fields with their own wish */
                if (has_filter('wccpf_before_fields_rendering')) {
                    $html = apply_filters('wccpf_before_fields_rendering', $field, $html);
                }
                
                do_action('wccpf_before_field_start', $field);
                
                $pHtml .= $html;
                
                do_action('wccpf_after_field_end', $field);               
            }
            $pHtml .= '</div>';
        }   
    
        return $pHtml;
	}
	
	/**
	 * 
	 * @param array $_group
	 * @return string
	 * 
	 */
	private function render_product_fields_with_custom_layout($_group) {
	    
	    wcff()->dao->set_current_post_type($_group["type"]);
	    $layout = wcff()->dao->load_layout_meta($_group["id"]); 
	    
	    $html = '<div class="wcff-fields-group" data-custom-layout="'. $_group["use_custom_layout"] .'" data-group-clonable="'. $_group["is_clonable"] .'">';
	    foreach ($layout["rows"] as $row) {
	        
	        if (!$this->determine_row_has_fields($row, $_group["fields"])) {
	            continue;
	        }
	        
	        $html .= '<div class="wcff-layout-form-row">';
	        foreach($row as $fkey) {

	            $html .= '<div class="wcff-layout-form-col" style="flex-basis: '. $layout["columns"][$fkey]["width"] .'%;">';
	                
    	            $field = $this->get_field_meta($fkey, $_group["fields"]);    	            
    	            if ($field) {
    	                if ($this->multilingual == "yes") {
    	                    /* Localize field */
    	                    //$field = wcff()->locale->localize_field($field);
    	                }
    	                if ($field["type"] == "datepicker" || $field["type"] == "colorpicker") {
    	                    $field["admin_class"] = $field["key"];
    	                    if ($field["type"] == "colorpicker") {
    	                        $this->color_fields[] = $field;
    	                    }
    	                    if ($field["type"] == "datepicker") {
    	                        $this->date_fields[] = $field;
    	                    }
    	                }
    	                
    	                if (WC()->session->__isset("wcff_validation_failed")) {
    	                    /* Last add to cart operation failed
    	                     * Try to restore the fields old value */
    	                    $index = "";
    	                    if ($this->is_cloning_enabled == "yes") {
    	                        $index= "_1";
    	                    }
    	                    if (isset($_REQUEST[$field["key"] . $index])) {
    	                        $field["default_value"] = $_REQUEST[$field["key"] . $index];
    	                    }
    	                }
    	                
    	                /* Inject label alignment property */
    	                $field["label_alignment"] = isset($_group["label_alignment"]) ? $_group["label_alignment"] : "left";
    	                
    	                /* Field rules script */
    	                if (isset($field["field_rules"]) && is_array($field["field_rules"]) && count($field["field_rules"]) != 0){
    	                    $this->fields_rules[$field["key"]] = $field["field_rules"];
    	                }
    	                
    	                /* Collecting Pricing rules meta */
    	                if (isset($field["pricing_rules"]) && is_array($field["pricing_rules"]) && count($field["pricing_rules"]) != 0) {
    	                    $this->pricing_rules[$field["key"]] = $field["pricing_rules"];
    	                }
    	                
    	                /* generate html for wccpf fields */
    	                $html .= wcff()->builder->build_user_field($field, "wccpf", $this->is_cloning_enabled, $_group["is_clonable"]);
    	            }
	            
	            $html .= '</div>';
	        }
	        $html .= '</div>';
	    }
	    $html .= '</div>';
	    return $html;
	}
	
	/**
	 * 
	 * @param string $_key
	 * @param object $_fields
	 * @return mixed|boolean
	 * 
	 */
	private function get_field_meta($_key, $_fields) {
	    foreach ($_fields as $field) {
	        if ($field["key"] == $_key) {
	            return $field;
	        }
	    }	    
	    return false;
	}
	
	/**
	 *  
	 * @param array $_fkeys
	 * @param object $_fields
	 * @return boolean
	 * 
	 */
	private function determine_row_has_fields ($_fkeys = array(), $_fields) {
	    foreach ($_fkeys as $_fkey) {
	        foreach ($_fields as $field) {
	            if ($field["key"] == $_fkey) {
	                return true;
	            }
	        }	        
	    }
	    return false;
	}
	
	/**
	 *
	 * Helper method for retrieving Admin Field's value
	 * If value no there then default value will be returned
	 * Except check box other fields value will be returned as it is,
	 * but for checkbox the value will be converted as Array and then returned
	 *
	 * @param object $_meta
	 * @param number $_id
	 * @param string $_ptype
	 * @return boolean|array| unknown|mixed|string
	 *
	 */
	private function determine_field_value($_meta, $_id = 0) {
	    $mval = false;
	    /**
	     * We are assuming that here the user will use whatever the Admin Fields that is placed for the product page
	     * not on the Product Taxonomies page or Admin Fields for variable sections. because it doesn't make any sense.
	     * and if they do then we have a problem here
	     */
	    if (metadata_exists("post", $_id, "wccaf_". $_meta["name"])) {
	        $mval = get_post_meta($_id, "wccaf_". $_meta["name"], true);
	        /* Incase of checkbox - the values has to be deserialzed as Array */
	        if ($_meta["type"] == "checkbox") {
	            $mval = explode(',', $mval);
	        }
	    } else {
	        /* This will make sure the following section fill with default value instead */
	        $mval = false;
	    }
	    /* We can trust this since we never use boolean value for any meta
	     * instead we use 'yes' or 'no' values */
	    if (!$mval) {
	        /* Value is not there - probably this field is not yet saved */
	        if ($_meta["type"] == "checkbox") {
	            $d_choices = array();
	            if ($_meta["default_value"] != "") {
	                $choices = explode(";", $_meta["default_value"]);
	                if (is_array($choices)) {
	                    foreach ($choices as $choice) {
	                        $d_value = explode("|", $choice);
	                        $d_choices[] = $d_value[0];
	                    }
	                }
	            }
	            $mval = $d_choices;
	        } else if ($_meta["type"] == "radio" || $_meta["type"] == "select") {
	            $mval = "";
	            if ($_meta["default_value"] != "") {
	                $d_value = explode("|", $_meta["default_value"]);
	                $mval = $d_value[0];
	            }
	        } else {
	            /* For rest of the fields - no problem */
	            $mval = isset($_meta["default_value"]) ? $_meta["default_value"] : "";
	        }
	    }
	    return $mval;
	}
	
	/**
	 * 
	 * @param string $position
	 * 
	 */
	private function handle_label_field($position = "beginning") {	    
	    foreach ($this->product_field_groups as $group) {
	        if (isset($group["fields"]) && count($group["fields"]) > 0) {
	            foreach ($group["fields"] as $field) {
	                if ($field["type"] == "label" && $field["position"] == $position) {	                    
	                    /* generate html for wccpf fields */
	                    $html = wcff()->builder->build_user_field($field, "wccpf");
	                    /* Allow third party apps logic to render wccpf fields with their own wish */
	                    if (has_filter('wccpf_before_fields_rendering')) {
	                        $html = apply_filters('wccpf_before_fields_rendering', $field, $html);
	                    }
	                    
	                    do_action('wccpf_before_field_start', $field);
	                    
	                    echo $html;
	                    
	                    do_action('wccpf_after_field_end', $field);	                    
	                }
	            }
	        }
	    }
	}
	
	/**
	 *
	 * @param WC_Product $_product
	 * @return integer
	 *
	 * Wrapper method for getting Wc Product object's ID attribute
	 *
	 */
	private function get_product_id($_product){
	    return method_exists($_product, 'get_id') ? $_product->get_id() : $_product->id;
	}
	
	/**
	 *
	 * Enqueue assets for Front end Product Page
	 *
	 * @param boolean $isdate_css
	 *
	 */
	public function enqueue_client_side_assets($isdate_css = false) { 
	    if (is_product() || is_cart() || is_checkout() && is_archive() || is_shop()) :
    	$wccpf_options = wcff()->option->get_options();
    	$field_glob_location = isset($wccpf_options["field_location"]) ? $wccpf_options["field_location"] : "woocommerce_before_add_to_cart_button"; ?>
		     
        <script type="text/javascript">	       
	    var wccpf_opt = {
	    	editable : "<?php echo isset( $wccpf_options["edit_field_value_cart_page"] ) ? $wccpf_options["edit_field_value_cart_page"] : "no" ?>",
	        cloning : "<?php echo isset( $wccpf_options["fields_cloning"] ) ? $wccpf_options["fields_cloning"] : "no"; ?>",
	        location : "<?php echo $field_glob_location; ?>",
	        validation : "<?php echo isset( $wccpf_options["client_side_validation"] ) ? $wccpf_options["client_side_validation"] : "no"; ?>",
	        validation_type : "<?php echo isset( $wccpf_options["client_side_validation_type"] ) ? $wccpf_options["client_side_validation_type"] : "submit"; ?>",
	        ajax_pricing_rules_title : "<?php echo isset( $wccpf_options["ajax_pricing_rules_title"] ) ? $wccpf_options["ajax_pricing_rules_title"] : "hide"; ?>",
	        ajax_pricing_rules_title_header : "<?php echo isset( $wccpf_options["ajax_pricing_rules_title_header"] ) ? $wccpf_options["ajax_pricing_rules_title_header"] : ""; ?>",
		    ajax_pricing_rules_price_container_is : "<?php echo isset( $wccpf_options["ajax_pricing_rules_price_container"] ) ? $wccpf_options["ajax_pricing_rules_price_container"] : "default"; ?>",
	        ajax_price_replace_container : "<?php echo isset( $wccpf_options["ajax_price_replace_container"] ) ? $wccpf_options["ajax_price_replace_container"] : ""; ?>",
	        price_details : "<?php echo isset( $wccpf_options["pricing_rules_details"] ) && $wccpf_options["pricing_rules_details"] == "show" ? true : false; ?>",
	        color_picker_functions    : [],
	        is_ajax_add_to_cart : "<?php echo get_option( 'woocommerce_enable_ajax_add_to_cart' ); ?>",
	        is_page : "<?php echo ( is_shop() ? "archive" : "single" ); ?>"
	    };
	    </script>	
	
		<?php
	        
		// Jquery ui and time picker style
		wp_enqueue_style("wcff-jquery-ui-style", wcff()->info['dir'].'assets/css/jquery-ui.css');
		wp_enqueue_style("wcff-timepicker-style", wcff()->info['dir'].'assets/css/jquery-ui-timepicker-addon.css');
		
		// Jquery init
		wp_enqueue_script("jquery");
		// jquery UI Core
		wp_enqueue_script('jquery-ui-core');
		// Jquery Date pciker
		wp_enqueue_script('jquery-ui-datepicker');
		
		// Jquery Multi-Language 
		wp_enqueue_script('jquery-ui-i18n', wcff()->info['dir'].'assets/js/jquery-ui-i18n.min.js');
		// Jquery Time Picker script
		wp_enqueue_script('jquery-ui-timepicker-addon', wcff()->info['dir'].'assets/js/jquery-ui-timepicker-addon.min.js');
		/* Moment for date parsing */
		wp_enqueue_script('moment', wcff()->info['dir'].'assets/js/moment.min.js');
		// Color Picker css
		wp_enqueue_style("wcff-colorpicker-style", wcff()->info['dir'].'assets/css/spectrum.css');
		// Color Picker Script
		wp_enqueue_script('wcff-colorpicker-script', wcff()->info['dir'].'assets/js/spectrum.js');
		// wcff Client css 
		wp_enqueue_style("wcff-client-style", wcff()->info['dir'].'assets/css/wcff-client.css');
		//wcff Client Script
		wp_enqueue_script('wcff-client-script', wcff()->info['dir'].'assets/js/wcff-client.js');
			
		?>
			
    	<?php if(is_shop()): ?>    		
    		<script>    		
    			/* Fix for the chinese character appearing on the datepicker */
    			jQuery(document).ready(function(jQuery){
    				jQuery.datepicker.setDefaults(jQuery.datepicker.regional["en"]);
        		});
        		
    			jQuery( document ).on( "click", ".wccpf_fields_table ", function(e){
        			var target = jQuery( e.target );
        			if( !target.is( ".wccpf_fields_table" ) && !target.is("input[type='checkbox']") && !target.is("input[type='radio']") && !( target.is( "label" ) && target.find("input[type='checkbox'],input[type='radio'],input[type='file']").length != 0 ) ){
						return false;
					}
            	});
    		</script>
    	<?php endif; ?>
	<?php endif; 
	}
	
	/**
	 * 
	 * Additional fiedls meta for Client Side rendering
	 * For special fields like DatePicker & Color picker
	 * Also meta for Fields Rules akso will be injected into DOM ENV
	 * 
	 */
	public function enqueue_wcff_client_side_meta($_echo = true) { 
	    $date_bucket = array();
	    $color_bucket = array();  
        foreach ($this->color_fields as $field) {                               
            $picker_meta = array();
            $picker_meta["color_format"] = isset($field["color_format"]) ? $field["color_format"] : "hex";
            $picker_meta["default_value"] = isset($field["default_value"]) ? $field["default_value"] : "#000";
            $picker_meta["show_palette_only"] = $field["show_palette_only"];
                
            if (isset($field["palettes"]) && $field["palettes"] != "") {
                $picker_meta["palettes"] = explode(";", $field["palettes"]);
            }	                    
            if (isset($field["color_image"]) && is_array($field["color_image"])) {
                $picker_meta["color_image"] = $field["color_image"];
			}	     
			
			if(isset($field["color_text_field"])){
				$picker_meta["color_text_field"] = $field["color_text_field"];
			}
            
            if (!empty($picker_meta)) {
                $color_bucket[$field["key"]] = $picker_meta;
            }       
        }
        foreach ($this->date_fields as $field) {
            $picker_meta = array();
            
            $localize = "none";
            $year_range = "-10:+10";            
            if (isset($field["language"]) && !empty($field["language"]) && $field["language"] != "default") {
                $localize = esc_attr($field["language"]);
            }
            if (isset($field["dropdown_year_range"]) && !empty($field["dropdown_year_range"])) {
                $year_range = esc_attr($field["dropdown_year_range"]);
            }
            
            /* Determine the current locale */
            $current_locale = wcff()->locale->detrmine_current_locale();
            /*If admin hadn't set locale, then try to determine */
            $localize = ($localize == "none") ? $current_locale : $localize;
            
            $picker_meta["localize"] = $localize;
            $picker_meta["year_range"] = $year_range;
            $picker_meta["admin_class"] = $field["admin_class"];

            if (isset($field["date_format"]) && $field["date_format"] != "") {
                $picker_meta["dateFormat"] = wcff()->builder->convert_php_jquery_datepicker_format(esc_attr($field["date_format"])) ."'";
            } else {
                $picker_meta["dateFormat"] = wcff()->builder->convert_php_jquery_datepicker_format("d-m-Y") ."'";
            }	
            
            $picker_meta["field"] = $field;            
            if (!empty($picker_meta)) {
                $date_bucket[$field["key"]] = $picker_meta;
            } 
        }
        
        if ($_echo) { ?>
            <script type="text/javascript">
			<?php 
			 if (is_product() && $this->product) : ?>
			 	var wcff_is_variable = "<?php echo $this->product->is_type("variable") ? "yes" : "no"; ?>";
				var wcff_product_price = <?php echo $this->product->get_price(); ?>;
			<?php endif; ?>
            	var wcff_date_picker_meta = <?php echo json_encode($date_bucket); ?>;
            	var wcff_color_picker_meta = <?php echo json_encode($color_bucket); ?>;
            	var wcff_fields_rules_meta = <?php echo json_encode($this->fields_rules); ?>;
            	var wcff_pricing_rules_meta = <?php echo json_encode($this->pricing_rules); ?>;
            </script>
        <?php       	  
        } else {
            $meta = array(
                "date_picker_meta" => $date_bucket,
                "color_picker_meta" => $color_bucket,
                "fields_rules_meta" => $this->fields_rules,
                "pricing_rules_meta" => $this->pricing_rules
            );
            return json_encode($meta);
        }        
	}
	
	/**
	 * 
	 * Loop through all groups and determine whether any group has show_title enabled.
	 * TRUE : Use global cloning title
	 * FALSE : Use group's title instead  
	 * 
	 * @return boolean
	 */
	private function use_global_cloning_title() {
	    foreach ($this->product_field_groups as $group) {
	        if (isset($group["fields"]) && count($group["fields"]) > 0) {	            
	            if ($group["show_title"] == "yes" && $group["is_clonable"] == "yes") {
	                return false;
	            }	            
	        }
	    }
	    return true;
	}	
	
	/**
	 * 
	 * @param object $_groups
	 * @return boolean
	 * 
	 */
	private function has_any_fields_to_render($_groups) {
	    $flaQ = false;
	    foreach ($_groups as $group) {
	        if (isset($group["fields"]) && count($group["fields"]) > 0) {
	            $flaQ = true;
	            break;
	        }
	    }    
	    return $flaQ;
	}
	
}

?>