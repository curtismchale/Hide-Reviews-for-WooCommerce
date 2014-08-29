<?php
/*
Plugin Name: Hide Reviews for WooCommerce
Plugin URI: http://sfndesign.ca/
Description: Hides all reviews in WooCommerce if you choose not to use the stars as ratings
Version: 1.0.1
Author: SFNdesign, Curtis McHale
Author URI: http://sfndesign.ca
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


require_once( 'inc/license.php' );

set_site_transient( 'update_plugins', null );
// this is the URL our updater / license checker pings. This should be the URL of the site with EDD installed
if ( ! defined( 'SFN_STORE_URL' ) ) {
	define( 'SFN_STORE_URL', 'http://sfndesign.ca' );
}

// the name of your product. This should match the download name in EDD exactly
define( 'SFN_HR_PLUGIN_NAME', 'Hide Reviews for WooCommerce' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

class Hide_Reviews_For_Woocommerce{

	function __construct(){

		add_action( 'widgets_init', array( $this, 'kill_wc_ratings_widgets' ), 11 );
		add_filter( 'woocommerce_product_tabs', array( $this, 'kill_wc_reviews_tab' ), 98 );

		add_action( 'admin_notices', array( $this, 'check_required_plugins' ) );

		add_action( 'admin_init', array( $this, 'plugin_updater' ) );

	} // construct

	/**
	 * Removes WC widgets we do not want
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @uses get_option()                   Gets option from the wp_options table give string
	 * @uses unregister_widget()            Unregisters the named widget
	 */
	public function kill_wc_ratings_widgets(){

		$review_setting = get_option( 'woocommerce_enable_review_rating' );

		if ( 'no' === $review_setting ){
			unregister_widget( 'WC_Widget_Top_Rated_Products' );
			unregister_widget( 'WC_Widget_Recent_Reviews' );
		}

	} // kill_wc_ratings_widgets

	/**
	 * Removes the WooCommerce Reviews
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @param array         $tabs           required            The tabs array
	 * @uses get_option()                                       Gets option from the wp_options table give string
	 * @return array        $tabs                               Our modified tabs array
	 */
	public function kill_wc_reviews_tab( $tabs ){

		$review_setting = get_option( 'woocommerce_enable_review_rating' );

		if ( 'no' === $review_setting ){
			unset( $tabs['reviews'] );
		}

		return $tabs;

	} // kill_wc_reviews_tab

	/**
	 * Checks for WooCommerce and GF and kills our plugin if they aren't both active
	 *
	 * @uses    function_exists     Checks for the function given string
	 * @uses    deactivate_plugins  Deactivates plugins given string or array of plugins
	 *
	 * @action  admin_notices       Provides WordPress admin notices
	 *
	 * @since   1.0
	 * @author  SFNdesign, Curtis McHale
	 */
	public function check_required_plugins(){

		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ){ ?>

			<div id="message" class="error">
				<p>Hide Reviews for WooCommerce expects WooCommerce to be active. This plugin has been deactivated.</p>
			</div>

			<?php
			deactivate_plugins( '/hide-reviews-for-woocommerce/hide-reviews-for-woocommerce.php' );
		} // if woocommerce

	} // check_required_plugins

	/**
	 * Checks for plugin updates based on the license key being valid
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @uses get_option()                   Gets given option from the WP DB
	 * @class EDD_Sl_Plugin_Updater()       EDD Plugin updater class
	 */
	public function plugin_updater() {

		// retrieve our license key from the DB
		$license_key = get_option( 'sfn_license' );
		$license_key = isset( $license_key['sfn_hr_license'] ) ? trim( $license_key['sfn_hr_license'] ) : null;

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( SFN_STORE_URL, __FILE__, array(
				'version'   => '1.0.1',                     // current version number
				'license'   => $license_key,                // license key (used get_option above to retrieve from DB)
				'item_name' => SFN_HR_PLUGIN_NAME,            // name of this plugin
				'author'    => 'SFNdesign, Curtis McHale',  // author of this plugin
			)
		);

	} // plugin_updater

} // Hide_Reviews_For_Woocommerce

$GLOBALS['hiderevwc'] = new Hide_Reviews_For_Woocommerce();
