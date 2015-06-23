<?php
class wp_support_settings {
	
	function __construct() {
		add_action( 'admin_menu', array( $this, 'wp_support_afo_menu' ) );
		add_action( 'plugins_loaded',  array( $this, 'wp_support_ticket_text_domain' ) );
		add_action( 'admin_init',  array( $this, 'wp_support_save_settings' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_support_name_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'wp_support_name_scripts' ) );
	}
	
	function wp_support_name_scripts() {
		wp_enqueue_style( 'bootstrap', plugins_url('assets/bootstrap.css', __FILE__));
		wp_enqueue_style( 'bootstrap-theme', plugins_url('assets/bootstrap-theme.css', __FILE__));
		wp_enqueue_script( 'jquery' );
	}
	
	function wp_support_ticket_text_domain(){
		load_plugin_textdomain('wst', FALSE, basename( dirname( __FILE__ ) ) .'/languages');
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
			<td colspan="2"><h1><?php _e('WP Support Settings','wst');?></h1></td>
		  </tr>
          <tr>
			<td colspan="2">&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top"><strong><?php _e('Admin Email','wst');?>:</strong></td>
			<td>
			<input type="text" name="support_admin_email" value="<?php echo $support_admin_email;?>" />
				<br />
				<i><?php _e('This mail will be used when support ticket related email are send. (When new ticket is created by user, User add a reply to a ticket etc.)','wst');?></i></td>
		  </tr>
		  <tr>
			<td valign="top"><strong><?php _e('From Email','wst');?>:</strong></td>
			<td>
			<input type="text" name="support_admin_from_email" value="<?php echo $support_admin_from_email;?>" />
				</td>
		  </tr>
          <tr>
			<td colspan="2">&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top"><strong><?php _e('Ticket Shortcode Page','wst');?>:</strong></td>
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
				?><font color="red"><?php _e('Important','wst');?></font>
				<br />
				<i><?php _e('Please select the page where you have entered the <strong>[ticket]</strong> shortcode','wst');?></i></td>
		  </tr>
		   <tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		  </tr>
		  <tr>
			<td valign="top"><strong><?php _e('Note','wst');?>*</strong></td>
			<td>
			<p>1. When ever you Update the "Ticket Shortcode Page" please update the <a href="options-permalink.php">permalink</a> settings. So that it can match with your current settings.</p>
			<p>2. If you are using permalinks for your site then your permalink structure should contain <strong>%supticket%</strong>.</p>
			<p>For example, if your permalink looks like this <strong>/%postname%/</strong> then you should change that to <strong>/%postname%/%supticket%/</strong>. This is not mandatory.</p>
			
			</td>
		  </tr>
		  <tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="submit" value="<?php _e('Save','wst');?>" class="button button-primary button-large" /></td>
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