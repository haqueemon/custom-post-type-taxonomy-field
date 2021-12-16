<?php

/**
 * Txm_posttype function
 * Register meta function
 */
class Txm_posttype{
	

	/**
	 * Construct function
	 * Load initial hooks for this class
	 */
	public function __construct(){

		add_action('admin_menu',array($this,'txm_add_metabox'));
		add_action('save_post',array($this,'txm_save_metabox'));
		add_action('save_post',array($this,'txm_save_image'));
		add_action('wp_loaded',array($this,'txm_action_hooks_pt'));

	}


	/**
	 * Load all action hooks depends on conditions
	 */
	function txm_action_hooks_pt(){


		$post_types = get_option('txm_post_types');

		/**
		 * Filters for Post Type
		 */
		// if(is_array($post_types) && in_array('post', $post_types)){
		// 	add_filter("manage_posts_columns",array($this,"txm_manage_post_types_table_column"));
		// 	add_filter ('manage_posts_custom_column', array($this,'txm_manage_post_types_table_custom_fields'), 10,3);
		// }

		/**
		 * Filters for Multiple Custom Post Types
		 */
		if(is_array($post_types)){
			foreach ($post_types as $post_type) {
				
				if($post_type!='post'){

					add_filter('manage_'.$post_type.'_posts_columns',array($this,'txm_manage_post_types_table_column'));
					add_filter ('manage_'.$post_type.'_posts_custom_column', array($this,'txm_manage_post_types_table_custom_fields'), 11,2);
				}
			}
		}


	}


	/**
	 * Display table column name for all post types
	 *
	 *
	 * @param  array $columns All default columns
	 * @return array
	 */
	function txm_manage_post_types_table_column($columns){
		unset($columns['date']);
		$columns['custom_field'] = __('Custom field','txm-meta');
		$columns['thumbnail'] = __('Thumbnail','txm-meta');
		$columns['is_feature'] = __('Is feature ?','txm-meta');
		$columns['date'] = __('Date','txm-meta');
		return $columns;
	}


	/**
	 * Display table column value for all post types
	 *
	 * @param  string $deprecated
	 * @param  string $column_name
	 * @param  string $post_id
	 * @return output
	 */
	function txm_manage_post_types_table_custom_fields($column_name,$post_id)
	{
		$metaValues = get_post_meta($post_id);

		if ($column_name == 'custom_field') {
			$custom_field = isset($metaValues['txm_custom_field_pt'][0]) ? $metaValues['txm_custom_field_pt'][0] : '';;
			echo $custom_field;
		}

		if ($column_name == 'thumbnail') {
			$image_url = isset($metaValues['txm_image_url_pt'][0]) ? '<img src="'.$metaValues['txm_image_url_pt'][0].'" width="80"/>' : '';;
			echo $image_url;
		}

		if ($column_name == 'is_feature') {

			$is_feature_pt = get_post_meta($post_id, 'txm_is_feature_pt', true);
			echo ($is_feature_pt==1) ? 'Yes' : 'No';
		}
	}


	/**
	 * Add Metabox to the selected post types
	 *
	 */
	function txm_add_metabox(){

		$post_types = get_option('txm_post_types');

		add_meta_box('txm_meta_fields',__('TXM meta fields','txm-meta'),array($this,'txm_display_func'),$post_types);

		add_meta_box('txm_meta_image',__('TXM meta image','txm-meta'),array($this,'txm_meta_image_func'),$post_types);

	}

	/**
	 * Display custom field & checkbox meta
	 *
	 * @param  array $post
	 * @return output
	 */
	function txm_display_func( $post ){

		// Get meta datas
		$txm_custom_field_pt = get_post_meta($post->ID,'txm_custom_field_pt',true);
		$txm_is_feature_pt = get_post_meta($post->ID,'txm_is_feature_pt',true);
		$checked = ($txm_is_feature_pt==1) ? 'checked' : '';


		// Nonce fiels for validation
		wp_nonce_field('txm_nonce','txm_nonce_field');

		// Input
		$txmPtLabel = __('Custom field : ','txm-meta');
		$txmIsFeaturelabel = __('Is Feature ? ','txm-meta' );


		$metabox_html = <<<EOD

			<p>
				<label class='txm-pt-label' for='txm_custom_field_pt'>{$txmPtLabel}</label>
				<input class='txm-pt-input' type='text' name='txm_custom_field_pt' id='txm_custom_field_pt' value='{$txm_custom_field_pt}' />
			</p>

			<p>
				<label class='txm-pt-label' for='txm_is_feature_pt'>{$txmIsFeaturelabel}</label>

				<span class='txm-pt-input'>
					<label class="txm-switch">
					  <input type='checkbox' name='txm_is_feature_pt' id='txm_is_feature_pt' value='1' {$checked} />
					  <span class="txm-slider round"></span>
					</label>
				</span>

			</p>

		EOD;


		echo $metabox_html;

	}


	/**
	 * Display image meta
	 *
	 * @param  array $post
	 * @return output
	 */
	function txm_meta_image_func( $post ){

		$image_id = get_post_meta($post->ID,'txm_image_id_pt',true);
		$image_url = get_post_meta($post->ID,'txm_image_url_pt',true);

		$label = __('Image :','txm-meta');
		$buttonLabel = __('Upload image','txm-meta');
		$txmButtonlabel = __('Upload image','txm-meta');

		wp_nonce_field('txm_image_nonce','txm_image_field_nonce');

		$metabox_html = <<<EOD

			<p>

				<label class='txm-pt-label' for='txm_is_feature_pt'>{$txmButtonlabel}</label>

				<span class='txm-pt-input'>
					<button class="button" id="upload_image_pt">{$buttonLabel}</button>
					<input type="hidden" name="txm_image_id_pt" id="txm_image_id_pt" value="{$image_id}" />
					<input type="hidden" name="txm_image_url_pt" id="txm_image_url_pt" value="{$image_url}" />
					<div class="txm-image-container"></div>
				</span>
			</p>

		EOD;

		echo $metabox_html;

	}


	/**
	 * Save custom field & checkbox meta
	 *
	 * @param  int $post_id
	 */
	function txm_save_metabox( $post_id ){

		if(!$this->is_secured('txm_nonce_field','txm_nonce',$post_id)){
			return $post_id;
		}

		$custom_field = isset($_POST['txm_custom_field_pt']) ? $_POST['txm_custom_field_pt'] : '';
		$feature = isset($_POST['txm_is_feature_pt']) ? $_POST['txm_is_feature_pt'] : 0;

		$custom_field = sanitize_text_field( $custom_field );

		update_post_meta($post_id, 'txm_custom_field_pt', $custom_field);
		update_post_meta($post_id, 'txm_is_feature_pt', $feature);

	}


	/**
	 * Save image meta
	 *
	 * @param  int $post_id
	 */
	function txm_save_image( $post_id ){

		if(!$this->is_secured('txm_image_field_nonce','txm_image_nonce',$post_id)){
			return $post_id;
		}

		$image_id = isset($_POST['txm_image_id_pt']) ? $_POST['txm_image_id_pt'] : '';
		$image_url = isset($_POST['txm_image_url_pt']) ? $_POST['txm_image_url_pt'] : '';

		update_post_meta($post_id,'txm_image_id_pt',$image_id);
		update_post_meta($post_id,'txm_image_url_pt',$image_url);

	}



	/**
	 * Nonce verification
	 */
	function is_secured($name,$action,$post_id){

		$nonce = isset($_POST[$name]) ? $_POST[$name] : '';

		if($nonce == ''){
			return false;
		}

		if(!wp_verify_nonce($nonce, $action)){
			return false;
		}

		if(wp_is_post_autosave($post_id)){
			return false;
		}

		if(wp_is_post_revision($post_id)){
			return false;
		}

		return true;
	}



}
new Txm_posttype();


?>