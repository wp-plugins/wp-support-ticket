<?php
class support_data_class{
	
	function __construct(){
 		add_action( 'init', array( $this, 'support_ticket_validate' ) );
		if(!session_id()){
			@session_start();
		}
	}
	
	function support_ticket_validate(){
	
		if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'add_ticket'){
			$error = false;
			$msg = '';
			$mc = new support_message_class;
			$ticket_subject = sanitize_text_field($_REQUEST['ticket_subject']);
			$ticket_body = esc_html($_REQUEST['ticket_body']);
			
			if(!is_user_logged_in()){
				$error = true;
				$msg .= __('Please login to create ticket.','wst').'<br>';
			}
			if(!$ticket_subject){
				$error = true;
				$msg .= __('Please enter ticket subject.','wst').'<br>';
			}
			if(!$ticket_body){
				$error = true;
				$msg .= __('Please enter ticket message.','wst').'<br>';
			}
			
			if(!$error){
				$data = array('ticket_subject' => $ticket_subject, 'ticket_body' => $ticket_body);
				$rc = new reply_class;
				$ret = $rc->insert_ticket($data);
				
				if(!$ret){
					$error = true;
					$msg = __('Error: Ticket cannot be created.','wst');
					$mc->add_message($msg,'bg-danger');
				} else {
					$msg = __('Support ticket created successfully.','wst');
					$mc->add_message($msg,'bg-success');
				}
			} else {
				$mc->add_message($msg,'bg-danger');
			}
		}
		
		if(isset($_REQUEST['action']) and $_REQUEST['action'] == 'add_reply'){
			$error = false;
			$msg = '';
			$mc = new support_message_class;
			
			$ticket_body = esc_html($_REQUEST['ticket_body']);
			
			if(!is_user_logged_in()){
				$error = true;
				$msg .= __('Please login to add reply.','wst').'<br>';
			}
			if(!$ticket_body){
				$error = true;
				$msg .= __('Please enter reply message.','wst').'<br>';
			}
			
			if(!$error){
				$data = array( 'ticket_body' => $ticket_body);
				$rc = new reply_class($_REQUEST['ticket_id']);
				$ret = $rc->insert_ticket_reply($data);
				
				if(!$ret){
					$error = true;
					$msg = __('Error: Reply cannot be created.','wst');
					$mc->add_message($msg,'bg-danger');
				} else {
					$msg = __('Reply added successfully.','wst');
					$mc->add_message($msg,'bg-success');
				}
			} else {
				$mc->add_message($msg,'bg-danger');
			}
		}
	}
	
}
new support_data_class;