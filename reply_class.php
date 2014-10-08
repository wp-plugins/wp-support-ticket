<?php
class reply_class {
	private $ticket_id;

	public function __construct($ticket_id=''){
		$this->ticket_id = $ticket_id;
		if(!session_id()){
			@session_start();
		}
	}
	
	public function get_user_tickets($user_id = ''){
		global $wpdb;
		if(!$user_id){
			return;
		}
		
		$st_title = sanitize_text_field( get_query_var( 'st_title' ) );
		
		$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;
		$args = array(
		'post_type' => 'ticket',
		'posts_per_page' => 10,
		'post_status' => 'publish',
		'author' => $user_id,
		'paged' => $paged,
		);
		
		if($st_title){
			$args['s'] = $st_title;
		}
		
		$tickets = new WP_Query( $args );
		return $tickets;
		
	}
	
	public function ticket_status_selected($sel = ''){
		global $ticket_status_array;
		if(is_array($ticket_status_array)){
			foreach($ticket_status_array as $key => $value){
				if($key == $sel){
					$ret .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
				} else {
					$ret .= '<option value="'.$key.'">'.$value.'</option>';
				}
			}
			return $ret;
		}
	}
	
	public function get_reply_data(){
		global $wpdb;
		if(!$this->ticket_id)
		return;
		
		$results = $wpdb->get_results( "SELECT * FROM `".$wpdb->prefix."support_reply` WHERE ticket_id = ".$this->ticket_id." ORDER BY `reply_added` DESC", OBJECT );
		if($results){
			return $results;
		} else {
			return;
		}
		
	}
	
	public function get_tickets_data(){
		global $wpdb;
		if(!$this->ticket_id)
		return;
		
		$results = $wpdb->get_results( "SELECT * FROM `".$wpdb->prefix."support_reply` WHERE ticket_id = ".$this->ticket_id." ORDER BY `reply_added` DESC", OBJECT );
		if($results){
			return $results;
		} else {
			return;
		}
		
	}
	
	
	public function get_attachments_data($reply_id){
		global $wpdb;
		if(!$reply_id)
		return;
		
		$results = $wpdb->get_results( "SELECT * FROM `".$wpdb->prefix."support_attachment` WHERE reply_id = ".$reply_id."", OBJECT );
		if($results){
			return $results;
		} else {
			return;
		}
		
	}
	
	public function get_reply_user_name($user_id = ''){
		if(!$user_id){
			return 'NA';
		}
		
		$user_info = get_userdata($user_id);
		if( $user_info ){
			return $user_info->display_name; 
		} else {
			return 'NA';
		}
	}
	
	
	public function reply_post_date($date){
		$date = strtotime($date);
		$date = date("F j, Y - g:i A",$date);
		return $date;
	}
	
	public function insert_ticket($data = array()){
		global $wpdb;
		// add ticket //
		$post = array(
		  'post_title'     => $data['ticket_subject'],
		  'post_status'    => 'publish',
		  'post_type'      => 'ticket',
		  'post_author'    => get_current_user_id(),
		  'post_date'      => date('Y-m-d H:i:s'),
		  'post_date_gmt'  => date('Y-m-d H:i:s')
		);  
		$new_ticket_id = wp_insert_post( $post, $wp_error ); 
		
		if(!$new_ticket_id){
			return false;
		}
		
		update_post_meta( $new_ticket_id, '_ticket_status', 1 );
		// add ticket //
		
		// add reply //
		$reply_data = array(
			'ticket_id' => $new_ticket_id,
			'user_id' => get_current_user_id(),
			'reply_from' => 'user', // for user 
			'reply_msg' => $data['ticket_body'],
			'reply_added' => date("Y-m-d H:i:s")
		);
		$wpdb->insert( $wpdb->prefix."support_reply", $reply_data ); 
		$reply_id = $wpdb->insert_id;
		// add reply //
		
		// add attachments //
		if(isset($_FILES["safile"])){	
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$uploadedfile = $_FILES['safile'];
			$upload_overrides = array( 'test_form' => false );
			
			add_filter('wp_handle_upload_prefilter', 'support_attachment_upload_filter' );
			
			$supported_types = array( 'image/jpeg',	'image/jpeg', 'image/png', 'image/gif', 'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip'	);
			$arr_file_type = wp_check_filetype($uploadedfile['name']);
			$uploaded_type = $arr_file_type['type'];
			
			// Check if the type is supported. If not, throw an error.
			if(in_array($uploaded_type, $supported_types)) {
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
			}
		 }
			 
		if($reply_id){
			if (file_exists($movefile['file'])) {
				$att_data = array(
				'reply_id' => $reply_id,
				'att_file' => $movefile['url']
				);
				$wpdb->insert( $wpdb->prefix."support_attachment", $att_data ); 
			}
		}
		// add attachments //
		
		
		// emails //
		// admin email // 
		$user_info = get_userdata(get_current_user_id());
		$headers = 'From: '.$user_info->user_login.' <'.$user_info->user_email.'>' . "\r\n";
		$message .= __('A new ticket is created.') . "\r\n\r\n";
		$message .= __('Message: ').$data['ticket_body']. "\r\n\r\n";
		wp_mail(get_option('support_admin_email'), $data['ticket_subject'], $message, $headers);
		
		// user email //
		$headers1 = 'From: '.get_bloginfo('name').' <'.get_option('support_admin_from_email').'>' . "\r\n";
		$message1 .= __('Hello,') . "\r\n";
		$message1 .= $user_info->user_login . "\r\n\r\n";
		$message1 .= __('Your new support ticket is successfully added.') . "\r\n\r\n";
		$message1 .= __('Thank You') . "\r\n\r\n";
		wp_mail($user_info->user_email, 'New support ticket', $message1, $headers1);
		// emails //
		
		return true;
	}
	
	public function insert_ticket_reply($data = array()){
		global $wpdb;
		if(!$this->ticket_id){
			return false;
		}
		// update ticket //
		update_post_meta( $this->ticket_id, '_ticket_status', 1 );
		// update ticket //
		
		// add reply //
		$reply_data = array(
			'ticket_id' => $this->ticket_id,
			'user_id' => get_current_user_id(),
			'reply_from' => 'user', // for user 
			'reply_msg' => $data['ticket_body'],
			'reply_added' => date("Y-m-d H:i:s")
		);
		$wpdb->insert( $wpdb->prefix."support_reply", $reply_data ); 
		$reply_id = $wpdb->insert_id;
		// add reply //
		
		// add attachments //
		
		if(isset($_FILES["safile"])){	
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$uploadedfile = $_FILES['safile'];
			$upload_overrides = array( 'test_form' => false );
			
			add_filter('wp_handle_upload_prefilter', 'support_attachment_upload_filter' );
			
			$supported_types = array( 'image/jpeg',	'image/jpeg', 'image/png', 'image/gif', 'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/pdf', 'application/zip'	);
			$arr_file_type = wp_check_filetype($uploadedfile['name']);
			$uploaded_type = $arr_file_type['type'];
			
			// Check if the type is supported. If not, throw an error.
			if(in_array($uploaded_type, $supported_types)) {
				$movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
			}
		 }
			 
		if($reply_id){
			if (file_exists($movefile['file'])) {
				$att_data = array(
				'reply_id' => $reply_id,
				'att_file' => $movefile['url']
				);
				$wpdb->insert( $wpdb->prefix."support_attachment", $att_data ); 
			}
		}
		// add attachments //
		
		
		// emails //
		// admin email // 
		$user_info = get_userdata(get_current_user_id());
		$headers = 'From: '.$user_info->user_login.' <'.$user_info->user_email.'>' . "\r\n";
		$message .= 'A new ticket reply is posted on '. get_the_title( $this->ticket_id ) . "\r\n\r\n";
		$message .= __('Message: ').$data['ticket_body']. "\r\n\r\n";
		wp_mail(get_option('support_admin_email'), 'New reply added', $message, $headers);
		// emails //
		
		return true;
	}
	
	public function reply_user_avatar($user_id){
		$default = '<img alt="anonymous" src="http://placehold.it/64x64">';
		if($user_id == ''){
			return $default;
		}
		if($user_id == 0){
			return $default;
		}
		
		$img = get_avatar( $user_id, 64 );
		if($img){
			return $img;
		} else {
			return $default;
		}
	}
	
	public function last_post_by($post_id = ''){
		global $wpdb;
		if(!$post_id){
			return 'NA';
		}
		$result = $wpdb->get_row( "SELECT * FROM `".$wpdb->prefix."support_reply` WHERE ticket_id = ".$post_id." ORDER BY `reply_added` DESC limit 1", OBJECT );
		
		if($result->user_id == ''){
			return 'NA';
		}
		
		$user_info = get_userdata($result->user_id);
		return $user_info->display_name;	
		
	}
	
}

function support_attachment_upload_filter( $file ){
	$file['name'] = time() . $file['name'];
	return $file;
}