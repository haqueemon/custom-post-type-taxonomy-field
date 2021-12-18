<?php

/**
* Plugin Name: TXM Basic Meta Fields
* Plugin URI: https://github.com/haqueemon/custom-post-type-taxonomy-field
* Description: Enjoy basic meta-fields like(Image, Is feature?, custom field) for Default and custom taxonomies with deafault and custom post types.
* Version: 1.0
* Author: Enamul Haque Emon
* Author URI: https://www.fiverr.com/emon7_7_7
* License:
* Text Domain:       txm-meta
* Domain Path:       /languages
*/

/**
 * Txm_main main class
 */
class Txm_main{
	

	/**
	 * Construct function
	 * Load all initial Hooks
	 */
	public function __construct(){

		add_action('plugins_loaded',array($this,'txm_loaded_plugins'));
		add_action('admin_enqueue_scripts',array($this,'txm_load_assets'));

	}


	/**
	 * Load Text-domain
	 */
	function txm_loaded_plugins(){

		load_plugin_textdomain("txm-meta",false,dirname(__FILE__)."/languages");
	}


	/**
	 * Load Assets
	 */
	function txm_load_assets($screen){
		// if(($screen=="edit-tags.php") OR ($screen=="term.php")){
			wp_enqueue_media();
			wp_enqueue_style( 'txm-admin-style', plugin_dir_url( __FILE__ ) . "assets/admin/css/style.css", null, time() );
			wp_enqueue_script( 'txm-admin-js', plugin_dir_url( __FILE__ ) . "assets/admin/js/main.js", 'jquery' , time(), true );
		// }
	}

	
}
new Txm_main();


/**
 * Include Settings Module
 */
require_once(plugin_dir_path(__FILE__)."module/txm-settings.php");


/**
 * Include Metabox Module
 */
require_once(plugin_dir_path(__FILE__)."module/txm-taxonomy.php");


/**
 * Include Post Type Metabox Module
 */
require_once(plugin_dir_path(__FILE__)."module/txm-post-type.php");
