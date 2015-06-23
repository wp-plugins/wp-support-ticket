<?php
class support_message_class {
	function __construct(){
		if(!session_id()){
			@session_start();
		}
	}
	
	function show_message(){
		if(isset($_SESSION['add_ticket_msg']) and $_SESSION['add_ticket_msg']){
			echo '<p class="'.$_SESSION['add_ticket_msg_class'].'">'.$_SESSION['add_ticket_msg'].'</p>';
			unset($_SESSION['add_ticket_msg']);
			unset($_SESSION['add_ticket_msg_class']);
		}
	}
	
	function add_message($msg = '', $class = ''){
		$_SESSION['add_ticket_msg'] = $msg;
		$_SESSION['add_ticket_msg_class'] = $class;		
	}
}