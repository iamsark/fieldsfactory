<?php 

if (!defined('ABSPATH')) {exit;}
/**
 *
 * @author 	    : Saravana Kumar K
 * @copyright   : Sarkware Research & Development (OPC) Pvt Ltd
 * 
 */

class wcff_cart_item_splitter {
    
    /* Fields cloning flaq */
    private $is_cloning_enabled = "no";
    /* Holds product fields list (from all group) */
    private $product_field_groups = null;
    /* Holds admin fields list (from all group) */
    private $admin_field_groups = null;
    
    public function __construct() {}
    
    public function split_cart_item($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        
        $wccpf_options = wcff()->option->get_options();
        $this->is_cloning_enabled = isset($wccpf_options["fields_cloning"]) ? $wccpf_options["fields_cloning"] : "yes";
        
        /* Get the last used template from session */
        $template = WC()->session->get("wcff_current_template", "single-product");
        
        $this->product_field_groups = wcff()->dao->load_fields_groups_for_product($product_id, 'wccpf', $template, "any");
        $this->admin_field_groups = wcff()->dao->load_fields_groups_for_product($product_id, 'wccaf', $template, "any");
        
        if (isset($variation_id) && $variation_id != 0 && !empty($variation_id)) {
            $wccvf_posts = wcff()->dao->load_fields_groups_for_product($variation_id, 'wccvf', $template, "any");
            $this->product_field_groups = array_merge( $this->product_field_groups, $wccvf_posts);
        }
        
        if ($this->is_cloning_enabled == "yes") {
            if ($quantity > 1) {
                
                /* Set the quanityt to 1 for original cart item */
                WC()->cart->set_quantity($cart_item_key, 1);
                /* Store the original cart data */
                $cart_item_custom_field = $cart_item_data;
                
                for ($i = 1; $i <= $quantity; $i++) {
                    
                    $cart_item_key_dup = false;
                    $cart_key = md5(microtime() . rand() . "wcff_cart_key_cloning");
                    $cart_item_data['unique_key'] = $cart_key;
                    
                    // Remove unwanted item data
                    $cart_item_data = $this->fetch_cart_item_data($cart_item_custom_field, $i, $quantity);
                    
                    if ($i != 1) {
                        $cart_item_key_dup = WC()->cart->add_to_cart($product_id, 1, $variation_id, $variation, $cart_item_data);
                    } else {
                        // remove old cart item and add new
                        if (WC()->cart->remove_cart_item($cart_item_key)) {
                            $cart_item_key_dup = WC()->cart->add_to_cart($product_id, 1, $variation_id, $variation, $cart_item_data);
                        }
                    }
                    // reset pricing rule
                    if ($cart_item_key_dup != false) {
                        wcff()->negotiator->handle_custom_pricing(wc()->cart->cart_contents[$cart_item_key_dup], $cart_item_key_dup);
                        // To remove cart item if validation is false
                        $validation = $this->split_validation($product_id, $cart_item_data, $cart_item_key_dup, $variation_id);
                        if (!$validation) {
                            if (method_exists( WC()->session, "set")) {
                                WC()->session->set("wcff_validation_failed", true);
                            }
                            WC()->cart->remove_cart_item($cart_item_key);
                        } else {
                            if (method_exists( WC()->session, "__unset")) {
                                WC()->session->__unset("wcff_validation_failed");
                            }
                        }
                    }                    
                }                
            }
        }        
    }
    
    private function fetch_cart_item_data($item_data, $index, $total) {
        
        foreach ($this->product_field_groups as $group) {
            if (count($group["fields"]) > 0) {
                foreach ($group["fields"] as $field) {
                    
                    $field ["cloneable"] = isset ( $field ["cloneable"] ) ? $field ["cloneable"] : "yes";
                    $field ["visibility"] = isset ( $field ["visibility"] ) ? $field ["visibility"] : "yes";
                    
                    for( $j = 0; $j < $total; $j++ ) {
                        $nindex = ($j+1);
                        if ($field ["cloneable"] == "yes" && $field ["visibility"] == "yes" && isset ( $item_data ['wccpf_' . $field ["key"] . "_" . $nindex] )) {
                            if( $index != $nindex ){
                                unset( $item_data ['wccpf_' . $field ["key"] . "_" . $nindex] );
                            } else {
                                if( $index != 1 ){
                                    $item_data ['wccpf_' . $field ["key"] . "_" . 1] = $item_data ['wccpf_' . $field ["key"] . "_" . $nindex];
                                    unset($item_data ['wccpf_' . $field ["key"] . "_" . $nindex]);
                                }
                                $item_data ['wccpf_' . $field ["key"] . "_" . 1]["fkey"] = $field ["key"] . "_" . 1;
                            }
                        } else if( $field ["cloneable"] == "yes" && $field ["visibility"] == "yes" && isset ( $item_data ['wccaf_' . $field ["key"] . "_" . $nindex] )){
                            if( $index != $nindex ){
                                unset( $item_data ['wccaf_' . $field ["key"] . "_" . $nindex] );
                            } else {
                                if( $index != 1 ){
                                    $item_data ['wccaf_' . $field ["key"] . "_" . 1] = $item_data ['wccaf_' . $field ["key"] . "_" . $nindex];
                                    unset($item_data ['wccaf_' . $field ["key"] . "_" . $nindex]);
                                }
                                $item_data ['wccaf_' . $field ["key"] . "_" . 1]["fkey"] = $field ["key"] . "_" . 1;
                            }
                        }
                    }
                    
                }
            }
        }       

        return $item_data;
    }
    
    private function validate($product_id, $cart_item_data, $_cart_key, $_variation_id) {
        
        $validation = array();
        foreach ($cart_item_data as $key => $val) {
            if (strpos($key, 'wccpf_') === 0 || strpos($key, 'wccaf_') === 0) {
                array_push( $validation, $this->validate_wccpf($product_id, $val["fkey"], $val["user_val"], $_cart_key, $_variation_id ));
            }
        }
        
        $flg = true;
        
        for( $o = 0; $o < count( $validation ); $o++ ){
            if( !$validation[$o]["status"] ){
                wc_add_notice( $validation[$o]["msg"], 'error');
                if( $flg  ){
                    $flg = false;
                }
            }
        }
        
        return $flg;
        
    }
    
    
}


?>