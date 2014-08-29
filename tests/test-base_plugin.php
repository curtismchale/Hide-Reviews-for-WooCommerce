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

}

