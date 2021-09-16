<?php

if (!defined('ABSPATH')) { exit; }

global $post;
$index = 0;

$logics = wcff()->dao->load_target_logics();
$contexts = wcff()->dao->load_target_contexts();

$group_rules = wcff()->dao->load_target_products_rules($post->ID);

$products = wcff()->dao->load_all_products();
array_unshift($products , array("id" => "-1", "title" => __("All Products", "wc-fields-factory")));

$pcats = wcff()->dao->load_product_categories();
array_unshift($pcats , array("id" => "-1", "title" => __("All Categories", "wc-fields-factory")));

?>

<div class="wcff_logic_wrapper">
	<table class="wcff_table">
		<tbody>
			<tr>
				<td class="summary">
					<label><?php _e( 'Rules', 'wc-fields-factory' ); ?></label>
					<p class="description"><?php _e( 'Add rules to determines which products or product categories will have this custom fields group', 'wc-fields-factory' ); ?></p>
				</td>
				<td>
					<div class="wcff_logic_groups">
					<?php if (is_array($group_rules) && count($group_rules) > 0 && !empty($group_rules)) {					
					    foreach ($group_rules as $rules) { ?>
																			
							<div class="wcff_logic_group"> 
								<h4><?php echo ($index == 0) ? __( 'Show this product fields group if', 'wc-fields-factory' ) : __( 'or', 'wc-fields-factory' ); ?></h4>
								<table class="wcff_table wcff_rules_table">
								<tbody>
									<?php foreach ($rules as $rule) { ?>
									<tr>
										<td>
											<select class="wcff_condition_param select">
												<?php foreach ($contexts as $context) {
													$selected = ($context["id"] == $rule["context"]) ? 'selected="selected"' : '';
													echo '<option value="'. $context["id"] .'" '. $selected .'>'. $context["title"] .'</option>';													
												} ?>																			
											</select>
										</td>
										<?php if( isset( $rule["logic"] ) ): ?>
										<td>
											<select class="wcff_condition_operator select">
												<?php foreach ($logics as $logic) {
													$selected = ($logic["id"] == $rule["logic"]) ? 'selected="selected"' : '';
													echo '<option value="'. $logic["id"] .'" '. $selected .'>'. $logic["title"] .'</option>';													
												} ?>												
											</select>
										</td>
										<?php endif; ?>
										<td class="condition_value_td">											
											<?php			
												if ($rule["context"] == "product") {
													echo wcff()->builder->build_products_selector('wcff_condition_value select', $rule["endpoint"]);
												} elseif ($rule["context"] == "product_cat") {
													echo wcff()->builder->build_products_category_selector('wcff_condition_value select', $rule["endpoint"]);
												} elseif ($rule["context"] == "product_tag") {
													echo wcff()->builder->build_products_tag_selector('wcff_condition_value select', $rule["endpoint"]);
												} elseif ($rule["context"] == "product_type") {
													echo wcff()->builder->build_products_type_selector('wcff_condition_value select', $rule["endpoint"]);
												} elseif ($rule["context"] == "product_variation") {
													echo wcff()->builder->build_product_variations_selector('wcff_condition_value select', $rule["endpoint"]);
												}
											?>											
										</td>
										<td class="add"><a href="#" class="condition-add-rule button"><?php _e( 'and', 'wc-fields-factory' ); ?></a></td>
										<td class="remove"><?php echo ($index != 0) ? '<a href="#" class="condition-remove-rule wcff-button-remove"></a>' : ''; ?></td>
									</tr>
									<?php $index++; } ?>
								</tbody>
							</table>
							</div>					
					
					<?php } } else { ?>					
						<div class="wcff_logic_group"> 
							<?php if ($post->post_type == "wccpf") : ?>
							<h4><?php _e( 'Show this product fields group if', 'wc-fields-factory' ); ?></h4>
							<?php elseif ($post->post_type == "wccaf") : ?>
							<h4><?php _e( 'Show this admin fields group if', 'wc-fields-factory' ); ?></h4>
							<?php elseif ($post->post_type == "wccvf") : ?>
							<h4><?php _e( 'Show this variation fields group if', 'wc-fields-factory' ); ?></h4>
							<?php endif; ?>
							<table class="wcff_table wcff_rules_table">
								<tbody>
									<tr>
										<td>
											<select class="wcff_condition_param select">
												<option value="products" selected="selected"><?php _e( 'Product', 'wc-fields-factory' ); ?></option>
												<option value="product_categories"><?php _e( 'Product Category', 'wc-fields-factory' ); ?></option>
												<option value="product_tags"><?php _e( 'Product Tag', 'wc-fields-factory' ); ?></option>
												<option value="product_types"><?php _e( 'Product Type', 'wc-fields-factory' ); ?></option>
												<option value="product_variations"><?php _e( 'Product Variation', 'wc-fields-factory' ); ?></option>
											</select>
										</td>
										<td>
											<select class="wcff_condition_operator select">
												<option value="==" selected="selected"><?php _e( 'is equal to', 'wc-fields-factory' ); ?></option>
												<option value="!="><?php _e( 'is not equal to', 'wc-fields-factory' ); ?></option>
											</select>
										</td>
										<td class="condition_value_td">
											<?php echo wcff()->builder->build_products_selector("wcff_condition_value select"); ?>											
										</td>
										<td class="add"><a href="#" class="condition-add-rule button"><?php _e( 'and', 'wc-fields-factory' ); ?></a></td>
										<td class="remove"></td>
									</tr>
								</tbody>
							</table>							
						</div>				
					<?php } ?>
						<h4>or</h4>
						<a href="#" class="condition-add-group button"><?php _e( 'Add condition group', 'wc-fields-factory' ); ?></a>	
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="wcff_condition_rules" id="wcff_condition_rules" value="Sample Rules"/>
</div>
