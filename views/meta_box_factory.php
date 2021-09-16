<?php 

if (!defined('ABSPATH')) { exit; }

global $post;

$wccpf_options = wcff()->option->get_options();
$is_multilingual = isset($wccpf_options["enable_multilingual"]) ? $wccpf_options["enable_multilingual"] : "no";
$supported_locale = isset($wccpf_options["supported_lang"]) ? $wccpf_options["supported_lang"] : array();
	
?>


<table class="wcff_table wcff_fields_factory_header">
	<tr>
		<td class="field-order wcff-sortable">
			<span class="wcff-field-order-number wcff-field-order">1</span>
		</td>
		<td class="field-label" style="<?php echo ($is_multilingual == "yes" && count($supported_locale) > 0) ? "padding-right: 25px;" : ""; ?>">
			<label class="wcff-field-label" data-key=""><input type="text" name="wcff-field-type-meta-label-temp" class="wcff-field-type-meta-label-temp" value=""></label>
			<?php
				if ($is_multilingual == "yes" && count($supported_locale) > 0) {
			        echo '<button class="wcff-factory-multilingual-label-btn" title="Open Multilingual Panel"><img src="'. (esc_url(wcff()->info["assets"] ."/img/translate.png")) .'"/></button>';
			        echo '<div class="wcff-factory-locale-label-dialog">';
			        $locales = wcff()->locale->get_locales();
			        foreach ($supported_locale as $code) {	
			            echo '<div class="wcff-locale-block" data-param="label">';
			            echo '<label>Label for '. $locales[$code] .'</label>';
			            echo '<input type="text"  name="wcff-field-type-meta-label-'. $code .'" class="wcff-field-type-meta-label-'. $code .'" value="" />';
			            echo '</div>';
			        }
			        echo '</div>';
			    }
			?>
		</td>
		<td class="field-name">
			<label class="wcff-field-name"></label>
		</td>
		<td class="field-type">
			<label class="wcff-field-type">
				<span style=""></span>
			</label>
		</td>
		<td class="field-actions">
			<div class="wcff-meta-option">
				<a href="#" data-key="" class="wcff-field-delete button">x</a>
			</div>
		</td>
	</tr>
</table>
<input type="hidden" name="wcff-field-order-index" class="wcff-field-order-index" value="1">
<div class="wcff_fields_factory wcff_fields_factory_config_wrapper">
<div class="wcff_fields_factory_config_container">
	<?php if ($post->post_type == "wccpf") : ?>
	<div class="wcff-factory-tab-header">
		<a href=".wcff-factory-tab-fields-meta" class="selected"><?php _e('Fields Meta', 'wc-fields-factory'); ?></a>		
		<a href=".wcff-factory-tab-pricing-rules" style=""><?php _e('Pricing Rules', 'wc-fields-factory'); ?></a>	
		<a href=".wcff-factory-tab-fields-rules" style=""><?php _e('Fields Rules', 'wc-fields-factory'); ?></a>
		<a href=".wcff-factory-tab-color-image" style="display: none;"><?php _e('Product Image', 'wc-fields-factory'); ?></a>
	</div>
	<?php endif; ?>

	<div class="wcff-factory-tab-container">
		<div class="wcff-field-types-meta-container wcff-factory-tab-child wcff-factory-tab-fields-meta"  style="display:block;">
			<table class="wcff_table">
				<tbody class="wcff-field-types-meta-body">				
					<?php //echo wcff()->builder->build_factory_fields("text", $post->post_type); ?>				
				</tbody>
			</table>
		</div>
		<?php if ($post->post_type == "wccpf") : ?>
		<div class="wcff-factory-tab-child wcff-factory-tab-pricing-rules"  style="display:none;">			
			<table class="wcff_table">
				<tbody class="wcff-field-types-meta-body">
					<tr>
						<td class="summary">
							<label for="post_type"><a href="https://sarkware.com/pricing-fee-rules-wc-fields-factory/" target="_blank" title="Documentation"><?php _e( 'Click here for Documentation', 'wc-fields-factory' ); ?></a></label>
							<br/>
							<label for="post_type"><?php _e( 'Pricing Rules', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( 'Change the product price whenever user submit the product along with this field', 'wc-fields-factory' ); ?></p>
							<br/>
							<label for="post_type"><?php _e( 'How it works', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( 'Use "Add Pricing Rule" button to add add a rule, specify the field value and the corresponding price, when the user submit the field with the given value while adding to cart, then the given price will be applied to the submitted product', 'wc-fields-factory' ); ?></p>
							<br/>
							<label for="post_type"><?php _e( 'Pricing Type', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( '<strong>Add :</strong> this option will add the given price with the product amount<br/><strong>Change :</strong> this option will replace the product original price with the given one', 'wc-fields-factory' ); ?></p>							
						</td>
						<td style="vertical-align: top;"  class="wcff-content-config-cell">
							<div class="wcff-tab-rules-wrapper price" class="wcff-factory-pricing-rules-wrapper">	
                                <div class="wcff-parent-rule-title"><?php _e( 'Pricing Rules', 'wc-fields-factory' ); ?></div>
                                <div class="wcff-rule-container">
                                    <div class="wcff-rule-container-is-empty"><?php _e( 'Pricing rule is empty!', 'wc-fields-factory' ); ?></div>
                                </div>																
								<input type="button" class="wcff-add-price-rule-btn button" value="<?php _e( 'Add Pricing Rule', 'wc-fields-factory' ); ?>">
							</div>
							<div class="wcff-tab-rules-wrapper fee" class="wcff-factory-fee-rules-wrapper">	
                                <div class="wcff-parent-rule-title"><?php _e( 'Fee Rules', 'wc-fields-factory' ); ?></div>	
                                <div class="wcff-rule-container">
                                    <div class="wcff-rule-container-is-empty"><?php _e( 'Fee rule is empty!', 'wc-fields-factory' ); ?></div>
                                </div>													
								<input type="button" class="wcff-add-fee-rule-btn button" class="button" value="<?php _e( 'Add Fee Rule', 'wc-fields-factory' ); ?>">
							</div>
							<input type="hidden" name="wcff_pricing_rules" class="wcff_pricing_rules" value="" />
							<input type="hidden" name="wcff_fee_rules" class="wcff_fee_rules" value="" />
						</td>
					</tr>					
				</tbody>
			</table>		
		</div>
		
		<div class="wcff-factory-tab-child wcff-factory-tab-fields-rules" style="display:none;">			
			<table class="wcff_table">
				<tbody class="wcff-field-types-meta-body">
					<tr>
						<td class="summary">
							<label for="post_type"><a href="https://sarkware.com/field-rule-wc-fields-factory/" target="_blank" title="Documentation"><?php _e( 'Click here for Documentation', 'wc-fields-factory' ); ?></a></label>
							<br/>
							<label for="post_type"><?php _e( 'Field Rules', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( 'Hide or show fields based on user interaction.', 'wc-fields-factory' ); ?></p>
							<br/>
							<label for="post_type"><?php _e( 'How it works', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( 'Use \'Add Field rule\' to add a field rule, specify the field value and select a condition. Then choose which are the field want to hide or show.', 'wc-fields-factory' ); ?></p>
							<br/>
							<label for="post_type"><?php _e( 'Rule Type', 'wc-fields-factory' ); ?></label>
							<p class="description"><?php _e( '<strong>Hide :</strong> Field will be hidden if the condition met. <br/><strong>Show :</strong> Field will be visible if the condition met.<br/><strong>Nill :</strong> Doesn\'t affect .', 'wc-fields-factory' ); ?></p>							
						</td>
						<td style="vertical-align: top;" class="wcff-content-config-cell">
							<div class="wcff-factory-field-rules-wrapper">		
                               <div class="wcff-parent-rule-title"><?php _e( 'Field Rules', 'wc-fields-factory' ); ?></div>	
                               <div class="wcff-rule-container">
                                   <div class="wcff-rule-container-is-empty"><?php _e( 'Field rule is empty!', 'wc-fields-factory' ); ?></div>
                               </div>																											
								<input type="button" class="wcff-add-field-rule-btn button wcff-add-field-rule-btn" value="<?php _e( 'Add Field Rule', 'wc-fields-factory' ); ?>">
							</div>
    					</td>
					</tr>					
				</tbody>
			</table>		
		</div>
		
		<div class="wcff-factory-tab-child wcff-factory-tab-color-image" style="display:none;">
               <table class="wcff_table">
    				<tbody class="wcff-field-types-meta-body">
    					<tr>
    						<td class="summary">
    							<label for="post_type"><a href="https://sarkware.com/field-rule-wc-fields-factory/" target="_blank" title="Documentation"><?php _e( 'Click here for Documentation', 'wc-fields-factory' ); ?></a></label>
    							<br/>
    							<label for="post_type"><?php  _e( 'Product Image', 'wc-fields-factory' ); ?></label>
    							<p class="description"><?php  _e( 'Choose your color pallet and perticular image for it.', 'wc-fields-factory' );  ?></p>
    							<br/>
    							<label for="post_type"><?php  _e( 'Choose Option', 'wc-fields-factory' ); ?></label>
    							<p class="description"><?php  _e( 'Choose image or color related another product.', 'wc-fields-factory' );  ?></p>
    						</td>
    						<td style="vertical-align: top;" class="wcff-content-config-cell">
    							<div class="wcff-tab-rules-wrapper color-image">		
                                   <div class="wcff-parent-rule-title"><?php _e( 'Product Image', 'wc-fields-factory' ); ?></div>	
                                   <div class="wcff-rule-container">
                                       <div class="wcff-rule-container-is-empty"><?php _e( 'Product Image rule is empty!', 'wc-fields-factory' ); ?></div>
                                   </div>																											
    								<input type="button" class="wcff-add-color-image-rule-btn button wcff-add-color-image-rule-btn" value="<?php _e( 'Add Field Rule', 'wc-fields-factory' ); ?>">
    							</div>
    						</td>
    					</tr>					
    				</tbody>
    			</table>		
    		</div>
		<?php endif; ?>
	</div>
</div>
</div>