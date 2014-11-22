<?php
class wp_support_settings {
	
	function __construct() {
		add_action( 'admin_menu', array( $this, 'wp_support_afo_menu' ) );
		add_action( 'admin_init',  array( $this, 'wp_support_save_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_support_name_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_support_name_scripts' ) );
	}
	
	function wp_support_name_scripts() {
		wp_enqueue_style( 'bootstrap', plugins_url('assets/bootstrap.css', __FILE__));
		wp_enqueue_style( 'bootstrap-theme', plugins_url('assets/bootstrap-theme.css', __FILE__));
		wp_enqueue_script( 'jquery' );
	}
	
	function  wp_support_afo_options() {
		global $wpdb;
		$support_admin_email = get_option('support_admin_email');
		$support_admin_from_email = get_option('support_admin_from_email');
		$ticket_sc_page = get_option('ticket_sc_page');
		?>
		<form name="f" method="post" action="">
		<input type="hidden" name="option" value="wp_support_save_settings" />
		<table width="100%" border="0" style="background:#FFFFFF; width:98%; padding:10px; margin-top:20px;"> 
		  <tr>
			<td width="25%"><h1>WP Support</h1></td>
			<td width="75%">&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top"><strong>Admin Email:</strong></td>
			<td>
			<input type="text" name="support_admin_email" value="<?php echo $support_admin_email;?>" />
				<br />
				<i>This mail will be used when support ticket related email are send. (When new ticket is created by user, User add a reply to a ticket etc.)</i></td>
		  </tr>
		  <tr>
			<td valign="top"><strong>From Email:</strong></td>
			<td>
			<input type="text" name="support_admin_from_email" value="<?php echo $support_admin_from_email;?>" />
				</td>
		  </tr>
		  <tr>
			<td valign="top"><strong>Ticket Shortcode Page:</strong></td>
			<td><?php
					$args = array(
					'depth'            => 0,
					'selected'         => $ticket_sc_page,
					'echo'             => 1,
					'show_option_none' => '-',
					'id' 			   => 'ticket_sc_page',
					'name'             => 'ticket_sc_page'
					);
					wp_dropdown_pages( $args ); 
				?>
				<br />
				<i>Please select the page where you have entered the <strong>[ticket]</strong> shortcode</i></td>
		  </tr>
		   <tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top"><strong>Note*</strong></td>
			<td>
			<p>1. When ever you change the ticket shortcode page please update your <a href="options-permalink.php">permalink</a> settings. So that it can match with your current settings.</p>
			<p>2. If you are using permalinks for your site then your permalink structure should contain <strong>%supticket%</strong>.</p>
			<p>For example, if your permalink looks like this <strong>/%postname%/</strong> then you should change that to <strong>/%postname%/%supticket%/</strong>. This is not mandatory.</p>
			
			</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="Save" class="button button-primary button-large" /></td>
		  </tr>
		</table>
		</form>
		<?php 
	}
	
	function wp_support_save_settings(){
		if(isset($_POST['option']) and $_POST['option'] == "wp_support_save_settings"){
			update_option( 'ticket_sc_page', sanitize_text_field($_POST['ticket_sc_page']) );
			update_option( 'support_admin_from_email', sanitize_text_field($_POST['support_admin_from_email']) );
			update_option( 'support_admin_email', sanitize_text_field($_POST['support_admin_email']) );
		}
	}
	
	function wp_support_afo_menu () {
		add_options_page( 'WP Support', 'WP Support', 'activate_plugins', 'wp_support_afo', array( $this, 'wp_support_afo_options' ));
	}
	
}
new wp_support_settings;