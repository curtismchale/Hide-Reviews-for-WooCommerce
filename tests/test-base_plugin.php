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

}

