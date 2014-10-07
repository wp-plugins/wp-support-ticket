<?php
class support_data_class{
	
	function __construct(){
 		add_action( 'init', array( $this, 'support_ticket_validate' ) );
		if(!session_id()){
			@session_start();
		}
	}
	
	function support_ticket_validate(){
	
		if($_REQUEST['action'] == 'add_ticket'){
			$error = false;
			$msg = '';
			$mc = new support_message_class;
			$ticket_subject = sanitize_text_field($_REQUEST['ticket_subject']);
			$ticket_body = esc_html($_REQUEST['ticket_body']);
			
			if(!is_user_logged_in()){
				$error = true;
				$msg .= 'Please login to create ticket.<br>';
			}
			if(!$ticket_subject){
				$error = true;
				$msg .= 'Please enter ticket subject.<br>';
			}
			if(!$ticket_body){
				$error = true;
				$msg .= 'Please enter ticket message.<br>';
			}
			
			if(!$error){
				$data = array('ticket_subject' => $ticket_subject, 'ticket_body' => $ticket_body);
				$rc = new reply_class;
				$ret = $rc->insert_ticket($data);
				
				if(!$ret){
					$error = true;
					$msg = 'Error: Ticket cannot be created.';
					$mc->add_message($msg,'bg-danger');
				} else {
					$msg = 'Support ticket created successfully.';
					$mc->add_message($msg,'bg-success');
				}
			} else {
				$mc->add_message($msg,'bg-danger');
			}
		}
		
		if($_REQUEST['action'] == 'add_reply'){
			$error = false;
			$msg = '';
			$mc = new support_message_class;
			
			$ticket_body = esc_html($_REQUEST['ticket_body']);
			
			if(!is_user_logged_in()){
				$error = true;
				$msg .= 'Please login to add reply.<br>';
			}
			if(!$ticket_body){
				$error = true;
				$msg .= 'Please enter reply message.<br>';
			}
			
			if(!$error){
				$data = array( 'ticket_body' => $ticket_body);
				$rc = new reply_class($_REQUEST['ticket_id']);
				$ret = $rc->insert_ticket_reply($data);
				
				if(!$ret){
					$error = true;
					$msg = 'Error: Reply cannot be created.';
					$mc->add_message($msg,'bg-danger');
				} else {
					$msg = 'Reply added successfully.';
					$mc->add_message($msg,'bg-success');
				}
			} else {
				$mc->add_message($msg,'bg-danger');
			}
		}
	}
	
}
new support_data_class;