<?php 

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

class wcff_post_list_table extends WP_List_Table {
	
    private $post_type = "";
	private $postPerPage = 50;
	
	public function __construct($_post_type = "wccpf") {
	   parent::__construct(
	       array(
	           'singular' => '',
	           'plural'   => 'posts',
	           'ajax'     => false,
	           'screen'   => get_current_screen()
	       )
	   );
	   $this->post_type = $_post_type;
	   
	   //add_action('manage_posts_extra_tablenav', array($this, 'inject_wcff_post_filters')); 
	   //add_filter('disable_months_dropdown', array($this, 'disable_month_filter'));
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see WP_List_Table::get_bulk_actions()
	 */
	public function get_bulk_actions() {
	    return array(
	       'trash' => 'Move to Trash'
	     );
	}
	
	public function extra_tablenav($which) {
	    
	}
	
	public function disable_month_filter() {
	    return true;
	}
	
	/**
	 *
	 * {@inheritDoc}
	 * @see WP_List_Table::prepare_items()
	 */
	public function prepare_items() { 
	    $status = isset($_GET["post_status"]) ? $_GET["post_status"] : 'publish';
	    $action = isset($_GET["action"]) ? $_GET["action"] : null;
	   
	    $columns = $this->get_columns();
	    $hidden = $this->get_hidden_columns();
	    $sortable = $this->get_sortable_columns();
	    
	    /* Fetch data */
	    $data = $this->load_wcff_group_posts($this->post_type, $status);
	    
	    usort( $data, array( &$this, 'sort_data' ) );
	    $currentPage = $this->get_pagenum();
	    $totalItems = count($data);
	    $this->set_pagination_args( array(
	        'total_items' => $totalItems,
	        'per_page'    => $this->postPerPage
	    ));
	    $data = array_slice($data,(($currentPage-1)*$this->postPerPage),$this->postPerPage);
	    $this->_column_headers = array($columns, $hidden, $sortable);
	    $this->items = $data;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see WP_List_Table::get_columns()
	 */
	public function get_columns() 	{
		$columns = array(
			'cb'		=> '<input type="checkbox" />',
			'id'          	=> 'ID',
			'title'       	=> 'Title',
			'fields'  => 'Fields'
		);
		return $columns;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function get_hidden_columns() {
		return array("id");
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see WP_List_Table::get_sortable_columns()
	 */
	public function get_sortable_columns() {
		return array('title' => array('title', false));
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see WP_List_Table::column_default()
	 */
	public function column_default($post, $column_name) {
	    switch ($column_name) {
	        case 'id':
	            return $post['id'];
	            break;
	        case 'fields':
	        do_action("manage_{$this->post_type}_posts_custom_column", $column_name, $post['id']);
	            break;
	    }
	}
	
	public function column_cb($_item) {
		return sprintf(
		    '<label class="screen-reader-text" for="'. $this->post_type .'_' . $_item['id'] . '">' . sprintf( __( 'Select %s' ), $_item['id'] ) . '</label>'
			. "<input type='checkbox' name='users[]' id='{$this->post_type}_{$_item['id']}' value='{$_item['id']}' />"
		);
	}
	
	public function column_title($_item) {
		$actions = array();
		$status = isset($_GET["post_status"]) ? $_GET["post_status"] : null;
		if (!$status || $status != "trash") {
			$actions['edit'] = '<a href="'. $_item["edit"] .'" aria-label="Edit \"'. $_item["title"] .'\"">Edit</a>';
			$actions['trash'] = $_item["trash"];
			//$actions['clone_group'] = $_item["clone"];
		} else {
			$actions['restore'] = $_item["untrash"];
			$actions['delete'] = $_item["delete"];
		}	
		if (!$status || $status != "trash") {
			return ('<a class="row-title" href="'. $_item["edit"] .'" aria-label="'. $_item["title"] .'">'. $_item["title"] .'</a>') . $this->row_actions($actions);
		} else {
			return ('<strong><span>'. $_item["title"] .'</span></strong>') . $this->row_actions($actions);
		}
		
	}
	
	/**
	 *
	 * Used to list all the custom variation fields group posts
	 *
	 * @return array
	 *
	 */
	private function load_wcff_group_posts($_post_type = 'post', $_status = 'publish') {
	    error_log("load_wcff_group_posts called");
		$res = array();
		$posts = get_posts(array (
		      'post_type' => $_post_type,
		      'post_status' => $_status,
			  'posts_per_page' => $this->postPerPage
			)
		);
		
		$posts = $this->apply_wcff_filters($posts);
		
		foreach ($posts as $post) {
			$res[] = array(
			    "id" => $post->ID,
			    "title" => $post->post_title,
			    "link" => get_post_permalink($post->ID),
			    "edit" => get_edit_post_link($post->ID),
				"trash" => sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				    '?page='. $this->page .'&post_type='. $_post_type .'&amp;action=trash&amp;post='.$post->ID,
					/* translators: %s: post title */
				    esc_attr(sprintf(__('Move &#8220;%s&#8221; to the Trash'), $post->post_title)),
					_x('Trash', 'verb')
				),
				"delete" => sprintf(
					'<a href="%s" class="submitdelete" aria-label="%s">%s</a>',
				    '?page='. $this->page .'&post_type='. $_post_type .'&amp;action=delete&amp;post='.$post->ID,
					/* translators: %s: post title */
				    esc_attr( sprintf(__('Delete &#8220;%s&#8221; permanently'), $post->post_title)),
					__('Delete Permanently')
				),
				"untrash" => sprintf(
					'<a href="%s" aria-label="%s">%s</a>',
				    '?page='. $this->page .'&post_type='. $_post_type .'&amp;action=untrash&amp;post='. $post->ID,
					/* translators: %s: post title */
				    esc_attr(sprintf(__('Restore &#8220;%s&#8221; from the Trash'), $post->post_title)),
					__('Restore')
				),
				
			    "fields_count" => $this->get_fields_count($post->ID)
			);
		}
		
		return $res;
	}
	
	private function apply_wcff_filters($_posts) {
	    
	    global $post;
	    
	    if ((isset($_GET["wcff_target_context_filter"]) && !empty($_GET["wcff_target_context_filter"])) &&
	        (isset($_GET["wcff_target_logic_filter"]) && !empty($_GET["wcff_target_logic_filter"])) &&
	        (isset($_GET["wcff_target_value_filter"]) && !empty($_GET["wcff_target_value_filter"]))) {
            $res = array();    
            $rule = array(
                array(
                    array(
                        "context" => $_GET["wcff_target_context_filter"],
                        "logic" => $_GET["wcff_target_logic_filter"],
                        "endpoint" => $_GET["wcff_target_value_filter"]
                    )
                )
            );
            
            foreach ($_posts as $p) {   
                setup_postdata($p);
                $post = $p;
                error_log("has term : ". has_term($_GET["wcff_target_value_filter"], 'product_cat', $post->ID) .", for : ".  $post->ID);
                if (has_term($_GET["wcff_target_value_filter"], 'product_cat', $post->ID)) {
                    error_log("Has term passed");
                } else {
                    error_log("Has term failed");
                }              
                
                if (wcff()->dao->check_for_product($post->ID, $rule)) {error_log("Rule matched");
                    $res[] = $post;
                } else {
                    error_log("Rule not matched");
                }
            }
            $_posts = $res;
        }
	    return $_posts;
	}
	
	private function load_value_filter($_context, $_selected_record) {
	    $html = '';
	    $records = array();
	    if ($_context == "products") {
	        $records = wcff()->dao->load_all_products();
	        array_unshift($records , array("id" => "", "title" => __("All Products", "wc-fields-factory")));
	    } else if ($_context == "product_categories") {
	        $records = wcff()->dao->load_product_categories();
	        array_unshift($records , array("id" => "", "title" => __("All Categories", "wc-fields-factory")));
	    } else if ($_context == "product_tags") {
	        $records = wcff()->dao->load_product_tags();
	        array_unshift($records , array("id" => "", "title" => __("All Tags", "wc-fields-factory")));
	    } else if ($_context == "product_types") {
	        $records = wcff()->dao->load_product_types();
	        array_unshift($records , array("id" => "", "title" => __("All Types", "wc-fields-factory")));
	    } else {
	        /* Ignore */
	    }
	    
	    foreach ($records as $record) {
	        $selected = ($record["id"] == $_selected_record) ? 'selected="selected"' : '';
	        $html .= '<option value="'. esc_attr($record["id"]) .'" '. $selected .'>'. esc_html($record["title"]) .'</option>';   
	    }
	    return $html;
	}
	
	private function sort_data($_a, $_b) {
		// Set defaults
		$orderby = 'title';
		$order = 'asc';
		// If orderby is set, use this as the sort column
		if(!empty($_GET['orderby'])) {
			$orderby = $_GET['orderby'];
		}
		// If order is set use this as the order
		if(!empty($_GET['order'])) {
			$order = $_GET['order'];
		}
		$result = strcmp( $_a[$orderby], $_b[$orderby] );
		if($order === 'asc') {
			return $result;
		}
		return -$result;
	}

}

?>