<?php
class create_support_sc_class{
	
	function __construct(){
		add_shortcode( 'create_support', array( $this, 'create_support_ticket_function' ) );
		if(!session_id()){
			@session_start();
		}
	}
	
	
	function create_support_ticket_function( $atts ) {
		$a = shortcode_atts( array(
			'title' => '',
		), $atts );
	
		if(is_user_logged_in()){
		ob_start();
		$mc = new support_message_class;
		$mc->show_message();
		?>
		<?php if($a['title']){?>
		<h2><?php echo $a['title']; ?> </h2>
		<?php } ?>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="add_ticket" />
		  <div class="form-group">
			<label for="exampleInputEmail1"><?php _e('Subject','wst');?></label>
			<input type="text" class="form-control" id="ticket_subject" name="ticket_subject" placeholder="<?php _e('Ticket Title','wst');?>">
		  </div>
		  <div class="form-group">
			<label for="exampleInputPassword1"><?php _e('Message','wst');?></label>
			<textarea class="form-control" name="ticket_body" rows="3"></textarea>
		  </div>
		  
		    <div class="form-group">
				<input type="file" name="safile" id="safile" />
		   </div>
		  
		  <button type="submit" class="btn btn-default"><?php _e('Submit','wst');?></button>
		</form>
		<?php
		$ret = ob_get_clean();	
		} else {
		$ret = __('Please login to create support ticket.','wst');	
		}
		return $ret;
	}
	
}

class ticket_sc_class{
	
	function __construct(){
		add_shortcode( 'ticket_details', array( $this, 'ticket_details_function' ) );
		add_shortcode( 'ticket_list', array( $this, 'ticket_list_function' ) );
		add_shortcode( 'ticket', array( $this, 'ticket_function' ) );
		add_shortcode( 'ticket_search', array( $this, 'ticket_search_function' ) );
		if(!session_id()){
			@session_start();
		}
	}
	
	function ticket_search_function( $atts ) {
		$a = shortcode_atts( array(
			'title' => '',
		), $atts );
		
		
		if(is_user_logged_in()){
		ob_start();
		$rc = new reply_class();
		?>
		<?php if($a['title']){?>
		<h2><?php echo $a['title']; ?> </h2>
		<?php } ?>
		<form action="<?php echo get_permalink(get_option('ticket_sc_page'));?>" method="get">
			<input type="hidden" name="action" value="srch_ticket" />
			  <div class="form-group">
				<label for="exampleInputPassword1"><?php _e('Ticket','wst');?></label>
				<input type="text" name="st_title" class="form-control"  />
			  </div>
		  <button type="submit" class="btn btn-default"><?php _e('Submit','wst');?></button>
		</form>
		<?php
		$this->get_ticket_reply($id);
		$ret = ob_get_clean();	
		} else {
		$ret = __('Please login to search ticket.','wst');	
		}
		return $ret;
	}
	
	function ticket_details_function( $atts ) {
		$a = shortcode_atts( array(
			'id' => '',
		), $atts );
		$id = $a['id'];
		if(!$id)
		return;
		
		if(is_user_logged_in()){
		ob_start();
		$ticket = get_post( $id );
		
		if(!$ticket){
			return;
		}
		if ( $ticket->post_author != get_current_user_id() )  {
			return;
		}
		global $ticket_status_array;
		$mc = new support_message_class;
		$mc->show_message();
		$ticket_status = get_post_meta( $id, '_ticket_status', true );
		?>
		<h2><?php echo get_the_title( $id ); ?> </h2>
		<h3><?php _e('Status','wst');?> <?php echo $ticket_status_array[$ticket_status]; ?> </h3>
		<form action="" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="add_reply" />
		    <input type="hidden" name="ticket_id" value="<?php echo $id;?>" />
			  <div class="form-group">
				<label for="exampleInputPassword1"><?php _e('Message','wst');?></label>
				<textarea class="form-control" name="ticket_body" rows="3"></textarea>
			  </div>
			  
			   <div class="form-group">
					<input type="file" name="safile" id="safile" />
		   	   </div>
		  
		  <button type="submit" class="btn btn-default"><?php _e('Submit','wst');?></button>
		</form>
		<?php
		$this->get_ticket_reply($id);
		$ret = ob_get_clean();	
		} else {
		$ret = __('Please login to view support ticket.','wst');	
		}
		return $ret;
	}
	
	function tick_details_link($id){
		if(!$id){
			return;
		}
		
		if(get_option('permalink_structure') != ''){
			$link = get_permalink(get_option('ticket_sc_page')).'details/'.$id;
		} else {
			$link = get_permalink(get_option('ticket_sc_page')).'?view=details&supticket='.$id;
		}
		return $link;
	}
	
	function ticket_list_function( $atts ) {
		$a = shortcode_atts( array(
			'title' => '',
		), $atts );
		$title = $a['title'];
		
		if(is_user_logged_in()){
		ob_start();
		global $ticket_status_array;
		$rc = new reply_class;
		$data = $rc->get_user_tickets(get_current_user_id());
		?>
		<?php if($title){?>
		<h2><?php echo $title;?></h2>
		<?php } ?>
		<table class="table table-hover">
		  <thead>
			<tr>
			  <th><?php _e('#','wst');?></th>
			  <th><?php _e('Ticket','wst');?></th>
			  <th><?php _e('Status','wst');?></th>
			  <th><?php _e('Last Post By','wst');?></th>
			</tr>
		  </thead>
		  <tbody>
		    <?php
		 	 if ( $data->have_posts() ) {
		 	 while ( $data->have_posts() ) { 
			 $data->the_post();
			 $ticket_status = get_post_meta( $data->post->ID, '_ticket_status', true );
			 ?>
				<tr>
				  <td><a href="<?php echo $this->tick_details_link($data->post->ID);?>"><?php echo $data->post->ID; ?></a></td>
				  <td><a href="<?php echo $this->tick_details_link($data->post->ID);?>"><?php echo get_the_title(); ?></a></td>
				  <td><?php echo $ticket_status_array[$ticket_status]; ?></td>
				  <td><?php echo $rc->last_post_by($data->post->ID); ?></td>
				</tr>
			<?php 
				} // end of loop
			} else { ?>
			<tr>
			  <td colspan="4" align="center"><?php _e('Support ticket not found','wst');?></td>
			</tr>
			<?php } ?>
		  </tbody>
		</table>
		
		<?php
		$big = 999999999;
		echo paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'prev_text' => __('Previous','wst'),
			'next_text' => __('Next','wst'),
			'current' => max( 1, get_query_var('paged') ),
			'total' => $data->max_num_pages
		) );
		$ret = ob_get_clean();	
		} else {
		$ret = __('Please login to view support ticket.','wst');	
		}
		return $ret;
	}
	
	function get_ticket_reply($id = ''){
		if(!$id)
		return;
		$rc = new reply_class( $id );
		$data = $rc->get_reply_data();
	
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
		}
	}
	
	
	function get_attachments($reply_id){
		$rc = new reply_class;
		$data = $rc->get_attachments_data($reply_id);
		if($data){
			_e('Attachments:','wst');
			foreach($data as $key => $value){
				echo '<a href="'.$value->att_file.'" target="_blank"><img style="border:none;" src="'.plugins_url('assets/attach.png', __FILE__).'"></a>';
			}
		}
	}
	
	function ticket_function( $atts ) {
		$a = shortcode_atts( array(
			'title' => '',
		), $atts );
		
		$ticket = get_query_var( 'supticket' );
		
		ob_start();
		if(!$ticket){
			$ret = $this->get_ticket_list($a['title']);
		} else {
			$ret = $this->get_ticket_details($ticket);
		}
		$ret = ob_get_clean();	
		return $ret;
	}
	
	function get_ticket_details($ticket = ''){
		echo do_shortcode('[ticket_details id="'.$ticket.'"]');
	}
	
	function get_ticket_list($title = ''){
		echo do_shortcode('[ticket_list title="'.$title.'"]');
	}
	
}



new create_support_sc_class;
new ticket_sc_class;