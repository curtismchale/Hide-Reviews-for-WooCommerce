<?php

class TestBasePlugin extends WP_UnitTestCase {

	private $plugin;

	function setUp(){
		parent::setUp();
		$this->plugin = $GLOBALS['hiderevwc'];
	}

	/**
	 * Makes sure our plugin class is in fact loaded
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testPluginActive(){
		$this->assertFalse( null == $this->plugin, 'testPluginActive says our plugin is not loaded' );
	}

	/**
	 * Makes sure that the reviews tab was removed from the array
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testRemoveReviewsTab(){
		update_option( 'woocommerce_enable_review_rating', 'no' );

		$tabs = array(
			'reviews' => 'something',
			'else'    => 'another value',
		);

		$returned_tabs = $this->plugin->kill_wc_reviews_tab( $tabs );

		$this->assertArrayNotHasKey( 'reviews', $returned_tabs, 'Reviews may not have been removed from the tabs' );

		delete_option( 'woocommerce_enable_review_rating' );
	}

	/**
	 * Makes sure that the reviews tab was NOT removed if the option isn't set for it to be removed
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testNotRemoveReviewsTab(){
		update_option( 'woocommerce_enable_review_rating', 'yes' );

		$tabs = array(
			'reviews' => 'something',
			'else'    => 'another value',
		);

		$returned_tabs = $this->plugin->kill_wc_reviews_tab( $tabs );

		$this->assertArrayHasKey( 'reviews', $returned_tabs, 'Reviews tab was removed when we expected it not to be removed' );

		delete_option( 'woocommerce_enable_review_rating' );
	}

	/**
	 * Tests to make sure that the deactivation routines are fired if we don't have our base plugins
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 */
	function testPluginDeactivate(){

		ob_start();
		$this->plugin->check_required_plugins();
		$s = ob_get_contents();
		ob_clean();

		$expected_output = $this->expected_output();

		// have to pass them through the same formatting to match against
		$s = preg_replace( '/\s+/', '', $s );
		$expected_output = preg_replace( '/\s+/', '', $expected_output );

		$this->assertTrue( $s === $expected_output );
	}

	/**
	 * Our expected HTML output when we check for plugin activation
	 *
	 * @since 1.0
	 * @author SFNdesign, Curtis McHale
	 *
	 * @return string
	 */
	function expected_output(){
		$html = '<div id="message" class="error">';
		$html .= '<p>Hide Reviews for WooCommerce expects WooCommerce to be active. This plugin has been deactivated.</p>';
		$html .= '</div>';
		return $html;
	}

	function tearDown(){
		parent::tearDown();
	}

}

