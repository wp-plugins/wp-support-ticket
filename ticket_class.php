<?php
class ticket_class {
	function __construct() {
		add_filter( 'manage_edit-ticket_columns', array($this,'show_ticket_fields') );
		add_action( 'manage_ticket_posts_custom_column' , array($this,'display_ticket_fields'), 10, 2 );
		add_action( 'init', array($this,'ticket_post') );
	}
	
	function ticket_post() {
		$labels = array(
			'name'               => _x( 'Ticket', 'post type general name', 'wst' ),
			'singular_name'      => _x( 'Ticket', 'post type singular name', 'wst' ),
			'menu_name'          => _x( 'Tickets', 'admin menu', 'wst' ),
			'name_admin_bar'     => _x( 'Ticket', 'add new on admin bar', 'wst' ),
			'add_new'            => _x( 'Add New', 'Ticket', 'wst' ),
			'add_new_item'       => __( 'Add New Ticket', 'wst' ),
			'new_item'           => __( 'New Ticket', 'wst' ),
			'edit_item'          => __( 'Edit Ticket', 'wst' ),
			'view_item'          => __( 'View Ticket', 'wst' ),
			'all_items'          => __( 'All Tickets', 'wst' ),
			'search_items'       => __( 'Search Tickets', 'wst' ),
			'not_found'          => __( 'No Ticket found.', 'wst' ),
			'not_found_in_trash' => __( 'No Ticket found in Trash.', 'wst' )
		);
	
		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'ticket' ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' )
		);
	
		register_post_type( 'ticket', $args );
	}
	
	
	function show_ticket_fields($columns) {
		$new_columns['cb'] = '<input type="checkbox" />';
		$new_columns['title'] = __('Title', 'wst');
		$new_columns['last_post'] = __('Last Post By','wst');
		$new_columns['status'] = __('Status','wst');
		return $new_columns;
	}
	
	function display_ticket_fields( $column, $post_id ){
		global $ticket_status_array;
		$rc = new reply_class;
		 switch ( $column ) {
			case 'last_post' :
			echo $rc->last_post_by($post_id);
			break;
			case 'status' :
			$status_id = get_post_meta( $post_id, '_ticket_status', true );
			echo $ticket_status_array[$status_id];
			break;
		}
	}

}

class ticket_meta_class {

	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'ticket_other_fields' ) );
		add_action( 'add_meta_boxes', array( $this, 'ticket_author_fields' ) );
		add_action( 'add_meta_boxes', array( $this, 'ticket_reply_box' ) );
		add_action( 'add_meta_boxes', array( $this, 'ticket_posts_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}
	
	
	public function ticket_other_fields( $post_type ) {
			$post_types = array('ticket');  
			if ( in_array( $post_type, $post_types )) {
				add_meta_box(
					'ticket_other_fields'
					,__( 'Status', 'wst' )
					,array( $this, 'render_ticket_other_fields' )
					,$post_type
					,'side'
					,'high'
				);
			}
	}
	
	public function ticket_author_fields( $post_type ) {
			$post_types = array('ticket');  
			if ( in_array( $post_type, $post_types )) {
				add_meta_box(
					'ticket_author_fields'
					,__( 'Author', 'wst' )
					,array( $this, 'render_ticket_author_fields' )
					,$post_type
					,'side'
					,'high'
				);
			}
	}
	
	public function ticket_posts_box( $post_type ) {
			$post_types = array('ticket');  
			if ( in_array( $post_type, $post_types )) {
				add_meta_box(
					'ticket_posts_box'
					,__( 'Posts', 'wst' )
					,array( $this, 'render_ticket_posts_box' )
					,$post_type
					,'advanced'
					,'high'
				);
			}
	}
	
	public function ticket_reply_box( $post_type ) {
			$post_types = array('ticket');  
			if ( in_array( $post_type, $post_types )) {
				add_meta_box(
					'ticket_reply_box'
					,__( 'Reply', 'wst' )
					,array( $this, 'render_ticket_reply_box' )
					,$post_type
					,'advanced'
					,'high'
				);
			}
	}
	

	public function save( $post_id ) {
		global $wpdb;
		if ( ! isset( $_POST['wpt_inner_custom_box_ticket_nonce'] ) )
			return $post_id;

		$nonce = $_POST['wpt_inner_custom_box_ticket_nonce'];

		if ( ! wp_verify_nonce( $nonce, 'wpt_inner_custom_box_ticket' ) )
			return $post_id;

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}
		
		$ticket_status = $_REQUEST['ticket_status'];
		update_post_meta( $post_id, '_ticket_status', $ticket_status );
		
		$reply_msg =  $_POST['reply_msg'];
		if($reply_msg){
			$data = array(
				'ticket_id' => $post_id,
				'user_id' => get_current_user_id(), 
				'reply_from' => 'admin', // for admin 
				'reply_msg' => $_REQUEST['reply_msg'],
				'reply_added' => date("Y-m-d H:i:s")
			);
			$wpdb->insert( $wpdb->prefix."support_reply", $data ); 
			$reply_id = $wpdb->insert_id;
		}
		
		
		// add attachments //
		function support_attachment_upload_filter_admin( $file ){
			$file['name'] = time() . $file['name'];
			return $file;
		}
		
		if(isset($_FILES["safile"])){	
			if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );
			$uploadedfile = $_FILES['safile'];
			$upload_overrides = array( 'test_form' => false );
			
			add_filter('wp_handle_upload_prefilter', 'support_attachment_upload_filter_admin' );
			
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
		// user email // 
		$post_author_id = get_post_field( 'post_author', $post_id );
		$user_info = get_userdata($post_author_id);
		$headers1 = 'From: '.get_bloginfo('name').' <'.get_option('support_admin_from_email').'>' . "\r\n";
		$message1 .= __('Hello,','wst') . "\r\n\r\n";
		$message1 .= __('A new ticket reply is posted on ','wst'). get_the_title( $post_id ) . "\r\n\r\n";
		$message1 .= __('Message:','wst').$_REQUEST['reply_msg']. "\r\n\r\n";
		$message1 .= __('Thank You','wst'). "\r\n\r\n";
		wp_mail($user_info->user_email, __('New ticket reply','wst'), $message1, $headers1);
		// emails //
		
	}
	
	
	public function render_ticket_other_fields( $post ) {
		wp_nonce_field( 'wpt_inner_custom_box_ticket', 'wpt_inner_custom_box_ticket_nonce' );
		$ticket_status = get_post_meta( $post->ID, '_ticket_status', true );
		$rc = new reply_class();
		?>
		<table width="100%" border="0">
		  <tr>
			<td>
				<select name="ticket_status">
					<?php echo $rc->ticket_status_selected($ticket_status);?>
				</select>
			</td>
		  </tr>
		</table>
		<?php
	}
	
	public function render_ticket_author_fields( $post ) {
		wp_nonce_field( 'wpt_inner_custom_box_ticket', 'wpt_inner_custom_box_ticket_nonce' );
		$rc = new reply_class();
		?>
		<table width="100%" border="0">
		<tr>
			<td>
				<?php _e('Author','wst'); ?>: <?php echo $rc->get_ticket_author($post->ID);?>
			</td>
		  </tr>
		</table>
		<?php
	}
	
	public function render_ticket_posts_box( $post ) {
		$rc = new reply_class( $post->ID );
		$data = $rc->get_reply_data();
		?>
		<table width="100%" border="0">
		  <tr>
			<td>
			<?php
			if($data){
				foreach($data as $key => $value){
			?>
				<div class="media">
                    <a href="#" class="pull-left">
                        <?php echo $rc->reply_user_avatar($value->user_id);?>
                    </a>
                    <div class="media-body">
                        <h4 class="media-heading"><?php echo $rc->get_reply_user_name($value->user_id);?>
                            <small><?php echo $rc->reply_post_date($value->reply_added);?></small>
                        </h4>
                        <?php echo $value->reply_msg;?>
						<div class=""><?php $this->get_attachments($value->reply_id);?></div>
                    </div>
                </div>
			<?php 
				} // end of foreach
			} else {
				_e('No reply posted yet.','wst');
			} ?>
			</td>
		  </tr>
		</table>
		<?php
	}
	
	function get_attachments($reply_id){
		$rc = new reply_class;
		$data = $rc->get_attachments_data($reply_id);
		if($data){
			_e('Attachments:','wst');
			foreach($data as $key => $value){
				echo '<a href="'.$value->att_file.'" target="_blank"><img border="0" src="'.plugins_url('assets/attach.png', __FILE__).'"></a>';
			}
		}
	}
	
	public function render_ticket_reply_box( $post ) {
		wp_nonce_field( 'wpt_inner_custom_box_ticket', 'wpt_inner_custom_box_ticket_nonce' );
		$ticket_status = get_post_meta( $post->ID, '_ticket_status', true );
		?>
		<table width="100%" border="0">
		  <tr>
			<td>
				<textarea name="reply_msg" style="width:100%; height:200px;"></textarea>
			</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td>
				<input type="file" name="safile" id="safile" />
			</td>
		  </tr>
		</table>
		<?php
	}
	
}


function call_ticket_meta_class() {
    new ticket_meta_class();
}

function update_edit_form_for_ticket_post() {
	echo ' enctype="multipart/form-data"';
}
add_action('post_edit_form_tag', 'update_edit_form_for_ticket_post');

if ( is_admin() ) {
    add_action( 'load-post.php', 'call_ticket_meta_class' );
    add_action( 'load-post-new.php', 'call_ticket_meta_class' );
	new ticket_class;
}