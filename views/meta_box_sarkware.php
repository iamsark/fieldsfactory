<?php 

global $post_type;
if ($post_type == "wccpf" || $post_type == "wccaf" || $post_type == "wccvf") { ?>
					
<script type="text/javascript">

(function($) {	
	
	$(document).ready(function(){		
		var wrapper = $('<div class="wcff-post-listing-column"></div>');
		wrapper.append( $('<div class="wcff-left-column"></div>') );
		$("#posts-filter, .subsubsub").wrapAll( wrapper );
		
		var wcff_message_box = '<div class="wcff-message-box">';
		wcff_message_box += '<div class="wcff-msg-header"><h3><?php _e( 'WC Fields Factory', 'wc-fields-factory' ); ?> <span><?php echo wcff()->info["version"]; ?></span></h3></div>';
		wcff_message_box += '<div class="wcff-msg-content">';
		wcff_message_box += '<h5><?php _e( 'Documentations', 'wc-fields-factory' ); ?></h5>';
		wcff_message_box += '<a href="https://sarkware.com/wc-fields-factory-a-wordpress-plugin-to-add-custom-fields-to-woocommerce-product-page/" title="<?php _e( 'Product Fields', 'wc-fields-factory' ); ?>" target="_blank"><?php _e( 'Product Fields', 'wc-fields-factory' ); ?></a>';
		wcff_message_box += '<a href="https://sarkware.com/add-custom-fields-woocommerce-admin-products-admin-product-category-admin-product-tabs-using-wc-fields-factory/" title="<?php _e( 'Admin Fields', 'wc-fields-factory' ); ?>" target="_blank"><?php _e( 'Admin Fields', 'wc-fields-factory' ); ?></a>';
		wcff_message_box += '<a href="https://sarkware.com/pricing-fee-rules-wc-fields-factory/" title="<?php _e( 'Pricing & Fee Rules', 'wc-fields-factory' ); ?>" target="_blank"><?php _e( 'Pricing & Fee Rules', 'wc-fields-factory' ); ?></a>';
		wcff_message_box += '<a href="https://sarkware.com/multilingual-wc-fields-factory/" title="<?php _e( 'Multilingual Setup', 'wc-fields-factory' ); ?>" target="_blank"><?php _e( 'Multilingual Setup', 'wc-fields-factory' ); ?></a>';		
		wcff_message_box += '<a href="https://sarkware.com/wc-fields-factory-api/" title="<?php _e( 'WC Fields Factory APIs', 'wc-fields-factory' ); ?>" target="_blank"><?php _e( 'WC Fields Factory APIs', 'wc-fields-factory' ); ?></a>';
		wcff_message_box += '<a href="https://sarkware.com/woocommerce-change-product-price-dynamically-while-adding-to-cart-without-using-plugins#override-price-wc-fields-factory" title="<?php _e( 'Override Product Prices', 'wc-fields-factory' ); ?>" target="_blank"><?php _e( 'Override Product Prices', 'wc-fields-factory' ); ?></a>';
		wcff_message_box += '<a href="https://sarkware.com/how-to-change-wc-fields-factory-custom-product-fields-rendering-behavior/" title="<?php _e( 'Rendering Behaviour', 'wc-fields-factory' ); ?>" target="_blank"><?php _e( 'Rendering Behaviour', 'wc-fields-factory' ); ?></a>';		
		wcff_message_box += '</div>';
		wcff_message_box += '<div class="wcff-msg-footer">';
		wcff_message_box += '<a href="https://sarkware.com" title="Sarkware" target="_blank">';
		wcff_message_box += '<img src="<?php echo wcff()->info["dir"]; ?>/assets/img/sarkware.png" alt="Sarkware" /> by Sarkware';
		wcff_message_box += '</a>';
		wcff_message_box += '</div>';		
		
		$(".wcff-post-listing-column").append( $('<div class="wcff-right-column">'+ wcff_message_box +'</div>') );
	});
	
})(jQuery);

</script>

<style type="text/css">
	#posts-filter p.search-box { display:none; }
</style>
    							
    	<?php		
}

?>