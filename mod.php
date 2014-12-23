<?php
add_action('init', 'wp_support_do_rewrite' );
function wp_support_do_rewrite(){
	$ticket_sc_page = get_option('ticket_sc_page');
	$anc_pages = get_post_ancestors($ticket_sc_page);
	$anc_page_title = array();
	$a_page = get_post($ticket_sc_page);
	$anc_page_title[] = $a_page->post_name;
	
	if(is_array($anc_pages) and count($anc_pages)){
		foreach($anc_pages as $key => $value){
			$a_page = get_post($value);
			$anc_page_title[] = $a_page->post_name;
		}
	}
	$anc_page_title = array_reverse($anc_page_title);
	$mod = implode('/',$anc_page_title);
	add_rewrite_rule('^'.$mod.'/details/([^/]*)/?','index.php?page_id='.$ticket_sc_page.'&view=details&supticket=$matches[1]','top');
	
}

add_action('init', 'custom_rewrite_tag', 10, 0);
function custom_rewrite_tag() {
	add_rewrite_tag('%supticket%', '([^&]+)');
}

function add_wp_support_query_vars_filter( $vars ){
  $vars[] = "st_title";
  return $vars;
}
add_filter( 'query_vars', 'add_wp_support_query_vars_filter' );