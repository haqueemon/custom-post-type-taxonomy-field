<?php

/**
 * Txm_settings main class
 * Chooses taxonomies for enable plugin features
 */
class Txm_settings{
	

	/**
	 * Construct function
	 * Load initial Custom Settings for this class
	 */
	function __construct(){

		add_action('admin_init',array($this,'txm_settings_func'));

	}

	/**
	 * Register Settings Field
	 */
	function txm_settings_func(){

		/**
		 * Register settings section
		 */
		add_settings_section('txm_setting',__('Txm-meta Plugin settings panel','txm-meta'),array($this,'txm_setting_hint'),'general');


		/**
		 * Register settings field for check taxonomies
		 */
		add_settings_field('txm_taxonomies',__('Select taxonomies','txm-meta'),array($this,'txm_display_taxonomies'),'general','txm_setting');
		register_setting('general','txm_taxonomies');


		/**
		 * Register settings field for all post types
		 */
		add_settings_field('txm_post_types',__('Select post types','txm-meta'),array($this,'txm_display_post_types'),'general','txm_setting');
		register_setting('general','txm_post_types');

	}


	/**
	 * Display setting section description
	 * @return (string) A paragraph for setting section.
	 */
	function txm_setting_hint(){
		$txm_section_hint = __('Settings panel for txm-meta plugin.','txm-meta');
		sprintf("<p>%s</p>",$txm_section_hint);
	}


	/**
	 * Display all taxonomy
	 * User can check/uncheck multiple taxonomies for enable/disable plugin features in the selected taxonomies.
	 *
	 * @return Checkboxes
	 */
	function txm_display_taxonomies(){

		$option = get_option('txm_taxonomies');

		$args = array(
		  'public'   => true,
		  '_builtin' => false
		); 
		$output = 'objects'; // or objects
		$operator = 'and'; // 'and' or 'or'
		$taxonomies = get_taxonomies( $args, $output, $operator );

		$catChecked = '';
		if(is_array($option) && in_array('category', $option)){
			$catChecked = 'checked';
		}

		$tagChecked = '';
		if(is_array($option) && in_array('post_tag', $option)){
			$tagChecked = 'checked';
		}

		echo "<label><input type='checkbox' ".$catChecked." name='txm_taxonomies[]' id='txm_taxonomies' value='category' />Category</label>&nbsp;&nbsp;&nbsp;";

		echo "<label><input type='checkbox' ".$tagChecked." name='txm_taxonomies[]' id='txm_taxonomies' value='post_tag' />Tag</label>&nbsp;&nbsp;&nbsp;";

		foreach ($taxonomies as $taxonomy) {

			$slug = $taxonomy->name;
			$label = $taxonomy->label;

			$checked = '';
			if(is_array($option) && in_array($slug, $option)){
				$checked = 'checked';
			}
			echo "<label><input type='checkbox' ".$checked." name='txm_taxonomies[]' id='txm_taxonomies' value='".$slug."' /> ".$label."</label>&nbsp;&nbsp;&nbsp;";
		}
	}



	/**
	 * Display all post types
	 * User can check/uncheck multiple post type for enable/disable plugin features in the selected post type.
	 *
	 * @return Checkboxes
	 */
	function txm_display_post_types(){

		$option = get_option('txm_post_types');

		$post_types = array('post' => 'Post');
		$args = array(
		   'public'   => true,
		   '_builtin' => false
		);
		  
		$output = 'objects';
		$operator = 'and';
		  
		$custom_post_types = get_post_types( $args, $output, $operator );
		  
		if ( $custom_post_types ) {

		  foreach ($custom_post_types as $key => $cpt) {

			$slug = $cpt->name;
			$label = $cpt->label;
		  	$post_types[$slug] = $label;

		  }
		  
		}

		foreach ($post_types as $key => $post_type) {

			$checked = '';
			if(is_array($option) && in_array($key, $option)){
				$checked = 'checked';
			}
			echo "<label><input type='checkbox' ".$checked." name='txm_post_types[]' id='txm_post_types' value='".$key."' /> ".$post_type."</label>&nbsp;&nbsp;&nbsp;";
		}
	}


}
new Txm_settings();

