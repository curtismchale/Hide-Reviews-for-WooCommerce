<?php
class SFN_HR_License{

	function __construct(){
		add_action('admin_menu', array( $this, 'sfn_hr_license_page' ) );
		add_action('admin_init', array( $this, 'register_wecr_option' ) );
		add_action( 'sfn_add_license_field', array( $this, 'add_plugin_license' ), 10, 2 );

		add_action('admin_init', array( $this, 'deactivate_license' ) );
		add_action('admin_init', array( $this, 'activate_license' ) );

	}


	/**
	 * Adds our license page to the submenu
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 * @access public
	 *
	 * @uses add_submenu_page()                 Adds a submenu to a WordPress admin menu
	 */
	public function sfn_hr_license_page() {
		add_submenu_page( 'woocommerce', 'SFN License', 'SFN License', 'manage_options', 'sfn-license', 'sfn_license_page' );
	}

	/**
	 * Adds our settings to the option table
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 * @access public
	 *
	 * @uses register_setting()             Registers the setting with WP
	 */
	public function register_wecr_option() {
		// creates our settings in the options table
		register_setting( 'sfn_license', 'sfn_license', array( $this, 'sanitize_license' ) );
	}

	/**
	 * Sanitizes the license during save
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @param string        $new            required            New license key
	 * @uses get_option()                                       Returns option from the db given key
	 * @uses update_option()                                    Updates given option
	 */
	public function sanitize_license( $new ) {

		$old = get_option( 'sfn_license' );
		$old = isset( $old['sfn_hr_license'] ) ? $old['sfn_hr_license'] : '';

		if( $old && $old != $new ) {
			$status = get_option( 'sfn_license_status' );
			$status = null;
			update_option( $status ); // new license has been entered, so must reactivate
		}

		return $new;
	}

	/**
	 * Adds the plugin license form
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 * @access public
	 *
	 * @param array         $license        required            All the SFN liceneses
	 * @uses get_option()                                       Returns given option from WP
	 * @uses esc_attr_e()                                       Escapes and echos
	 * @uses wp_nonce_field()                                   Creates a nonce for us
	 */
	public function add_plugin_license( $license ){

		$license = isset( $license['sfn_hr_license'] ) ? $license['sfn_hr_license'] : false;
		$status = get_option( 'sfn_hr_license_status' );

	?>

		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e('Hide Reviews for WooCommerce'); ?>
					</th>
					<td>
						<input id="sfn_license[sfn_hr_license]" name="sfn_license[sfn_hr_license]" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
						<label class="description" for="sfn_license[sfn_hr_license]"><?php _e('Enter your license key'); ?></label>
					</td>
				</tr>
				<?php if ( false !== $license ) { ?>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('Activate License'); ?>
						</th>
						<td>
							<?php if( $status !== false && $status == 'valid' ) { ?>
								<span style="color:green;"><?php _e('active'); ?></span>
								<?php wp_nonce_field( 'wecr_deactivate_status_nonce', 'wecr_deactivate_status_nonce' ); ?>
								<input type="submit" class="button-secondary edd_license_deactivate" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
							<?php } else {
								wp_nonce_field( 'wecr_activate_status_nonce', 'wecr_activate_status_nonce' ); ?>
								<input type="submit" class="button-secondary edd_license_activate" name="edd_license_activate" value="<?php _e('Activate License'); ?>"/>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>

	<?php
	}

	/**
	 * License deactivation routine
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 * @access public
	 *
	 * @uses check_admin_referer()              Checks the given nonce param
	 * @uses get_option()                       Returns option given string
	 * @uses home_url()                         echos home url of the site
	 * @uses wp_remote_get()                    Makes http get request
	 * @uses add_query_arg()                    Adds query args to a URL
	 * @uses is_wp_error()                      Returns true if object is WP_ERROR
	 * @uses wp_remote_retriev_body()           Gets the HTML body of a page retrieved
	 * @uses delete_option()                    Deletes given option
	 */
	public function deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['edd_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'wecr_deactivate_status_nonce', 'wecr_deactivate_status_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = get_option( 'sfn_license' );
			$license = trim($license['sfn_hr_license'] );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( WECR_PLUGIN_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, SFN_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' )
				delete_option( 'sfn_sfn_hr_license_status' );

		}
	}

	/**
	 * License activation routine
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 * @access public
	 *
	 * @uses check_admin_referer()              Checks the given nonce param
	 * @uses get_option()                       Returns option given string
	 * @uses home_url()                         echos home url of the site
	 * @uses wp_remote_get()                    Makes http get request
	 * @uses add_query_arg()                    Adds query args to a URL
	 * @uses is_wp_error()                      Returns true if object is WP_ERROR
	 * @uses wp_remote_retriev_body()           Gets the HTML body of a page retrieved
	 * @uses update_option()                    updates given option
	 */
	function activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['edd_license_activate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'sfn_hr_activate_status_nonce', 'sfn_hr_activate_status_nonce' ) ){
				return; // get out if we didn't click the Activate button
			}

			// retrieve the license from the database
			$license = get_option( 'sfn_license' );
			$license = trim( $license['sfn_hr_license'] );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( SFN_HR_PLUGIN_NAME ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_get( add_query_arg( $api_params, SFN_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );
			print_r( $response );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ){
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'sfn_hr_license_status', $license_data->license );

		}
	} // if $_POST

} // WECR_License

new SFN_HR_license();

if ( ! function_exists( 'sfn_license_page' ) ){

	/**
	 * Base for plugin activations pages
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @uses get_option()           Returns given option from the DB
	 * @uses settings_fields()      silly settings api stuff
	 * @uses submit_button()        Gives us a settings button out of the WP settings api
	 */
	function sfn_license_page(){

		$license = get_option( 'sfn_license' );

		?>
		<div class="wrap">
			<h2><?php _e('SFN License'); ?></h2>
			<form id="sfn-license-form" method="post" action="options.php">

				<?php settings_fields('sfn_license'); ?>

				<?php do_action( 'sfn_add_license_field', $license ); ?>

				<?php submit_button(); ?>

			</form>
		<?php

	} // sfn_license_page

} // if function_exists
