<?php 
/**
 * 
 * @author 		: sark
 * @copyright	: Sarkware Research & Development (OPC) Pvt Ltd
 *
 */

class wcff_api {
	
	/**
	 * 
	 * Returns all the Post of type wccaf
	 * @return array
	 * 
	 */
	public function loadAdminFieldsGroup() {
		
	}
	
	/**
	 *
	 * Returns all the Post of type wccpf
	 * @return array
	 *
	 */
	public function loadProductFieldsGroup() {
		
	}
	
	/**
	 *
	 * Returns all the Post of type wcccf
	 * @return array
	 *
	 */
	public function loadCheckoutFieldsGroup() {
		
	}
	
	/**
	 *
	 * Returns the Meta of a particular admin field
	 *
	 * @param integer $_wccaf_id
	 * @param string $_field_handle
	 * @return object
	 *
	 */
	public function loadAdminField($_wccaf_id, $_field_handle) {
	    
	}
	
	/**
	 *
	 * Returns the Meta of all fields of a particular admin fields group
	 *
	 * @param integer $_wccaf_id
	 * @return array
	 *
	 */
	public function loadAdminFields($_wccaf_id) {
	    
	}
	
	/**
	 * 
	 * Returns the Meta of a particular product field
	 * 
	 * @param integer $_wccpf_id
	 * @param string $_field_handle
	 * @return object
	 * 
	 */
	public function loadProductField($_wccpf_id, $_field_handle) {
		
	}
	
	/**
	 * 
	 * Returns the Meta of all fields of a particular product fields group
	 * 
	 * @param integer $_wccpf_id
	 * @return array
	 * 
	 */
	public function loadProductFields($_wccpf_id) {
		
	}
	
	/**
	 *
	 * Returns the Meta of a particular checkout field
	 *
	 * @param integer $_wcccf_id
	 * @param string $_field_handle
	 * @return object
	 *
	 */
	public function loadCheckoutField($_wcccf_id, $_field_handle) {
	    
	}
	
	/**
	 *
	 * Returns the Meta of all fields of a particular checkout fields group
	 *
	 * @param integer $_wcccf_id
	 * @return array
	 *
	 */
	public function loadCheckoutFields($_wcccf_id) {
	    
	}
	
	/**
	 * 
	 * Returns All custom Fields (wccpf, wccaf) Values of all line items of an Order
	 * 
	 * @param integer $_order_id
	 * @return array
	 * 
	 */
	public function getOrderMetaValues($_order_id) {
	    
	}
	
	/**
	 * 
	 * Returns All custom Fields Values of a particular line item of an Order
	 * 
	 * @param integer $_order_id
	 * @param integer $_item_id
	 * @return array
	 * 
	 */
	public function getOrderItemMetaValues($_order_id, $_item_id) {
	    
	}
	
	/**
	 * 
	 * Returns Value of a Custom field of a particular line item of an Order
	 * 
	 * @param integer $_order_id
	 * @param integer $_item_id
	 * @param string $_field_handle
	 * @return mixed
	 * 
	 */
	public function getOrderItemMetaValue($_order_id, $_item_id, $_field_handle) {
	    
	}
	
	/**
	 * 
	 * Returns all custom product (wccpf alone) fields values of all line items of an Order
	 * 
	 * @param integer $_order_id
	 * @return array
	 * 
	 */
	public function getProductFieldValue($_order_id) {
	    
	}
	
	/**
	 * 
	 * Returns the value of a custom product field values of all line items of an Order
	 * 
	 * @param integer $_order_id
	 * @param integer $_field_handle
	 * @return array
	 * 
	 */
	public function getProductFieldValue($_order_id, $_field_handle) {
	    
	}
	
	/**
	 * 
	 * @param integer $_order_id
	 * @param integer $_item_id
	 * @param integer $_field_handle
	 * @return mixed
	 * 
	 */
	public function getProductFieldValue($_order_id, $_item_id, $_field_handle) {
	    
	}
	
	public function getProductAdminFieldValue() {
	    
	}
	
	public function getVariationAdminFieldValue() {
	    
	}
	
	public function getVariationAdminFieldValue() {
	    
	}
	
	public function getCategoryAdminFieldValue() {
	    
	}
}

?>