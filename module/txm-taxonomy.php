<?php

/**
 * Txm_taxonomy function
 * Register meta function
 */
class Txm_taxonomy{
	

	/**
	 * Construct function
	 * Load initial hooks for this class
	 */
	public function __construct(){

		add_action('wp_loaded',array($this,'txm_action_hooks'));
		add_action('init',array($this,'txm_register_meta'));

	}

	/**
	 * Load all action hooks depends on conditions
	 */
	function txm_action_hooks(){

		/**
		 * Get all selected taxonomies
		 */
		$allTaxonomies = get_option('txm_taxonomies');

		/**
		 * Get custom taxonomies
		 */
		$args = array(
		  'public'   => true,
		  '_builtin' => false,
		); 
		$output = 'objects'; // or objects
		$operator = 'and'; // 'and' or 'or'
		$taxonomies = get_taxonomies( $args, $output, $operator );


		/**
		 * Filters & Actions for Category Taxonomy
		 */
		if(is_array($allTaxonomies) && in_array('category', $allTaxonomies)){
			add_filter('manage_edit-category_columns', array($this,'txm_manage_table_column'));
			add_filter('manage_category_custom_column',  array($this,'txm_manage_table_custom_fields'), 10,3);
			
			add_action('category_add_form_fields', array($this,'txm_display_custom_fields'));

			add_action('create_category', array($this,'txn_create_custom_field'));

			add_action('category_edit_form_fields', array($this,'txm_display_edit_custom_field'));

			add_action('edit_category', array($this,'txn_edit_custom_field'));
		}


		/**
		 * Filters & Actions for Tag Taxonomy
		 */
		if(is_array($allTaxonomies) && in_array('post_tag', $allTaxonomies)){
			$post_type = "post_tag";
			add_filter('manage_edit-'.$post_type.'_columns',array($this,'txm_manage_table_column'));
			add_filter ('manage_post_tag_custom_column', array($this,'txm_manage_table_custom_fields'), 10,3);

			add_action('post_tag_add_form_fields',array($this,'txm_display_custom_fields'));

			add_action('create_post_tag',array($this,'txn_create_custom_field'));

			add_action('post_tag_edit_form_fields',array($this,'txm_display_edit_custom_field'));

			add_action('edit_post_tag',array($this,'txn_edit_custom_field'));
		}


		/**
		 * Filters & Actions for Multiple custom taxonomies
		 */
		if(is_array($taxonomies)){
			foreach ($taxonomies as $taxonomy) {
				$slug = $taxonomy->name;
				$label = $taxonomy->label;
				if(in_array($slug, $allTaxonomies)){
					$post_type = $slug;
					add_filter('manage_edit-'.$post_type.'_columns',array($this,'txm_manage_table_column'));
					add_filter ('manage_'.$post_type.'_custom_column', array($this,'txm_manage_table_custom_fields'), 10,3);

					$action = $slug."_add_form_fields";
					add_action($action,array($this,'txm_display_custom_fields'));

					$action = "create_".$slug;
					add_action($action,array($this,'txn_create_custom_field'));

					$action = $slug."_edit_form_fields";
					add_action($action,array($this,'txm_display_edit_custom_field'));

					$action = "edit_".$slug;
					add_action($action,array($this,'txn_edit_custom_field'));
				}
			}
		}

	}



	/**
	 * Register meta function
	 */
	function txm_register_meta(){
		$arguments1 = array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'single' => true,
			'descript' => 'Custom meta field for all taxonomies',
			'show_in_rest' => true
		);
		register_meta( 'term', 'txm_custom_field', $arguments1 );

		$arguments2 = array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'single' => true,
			'description' => 'Custom meta image id field for all taxonomies',
			'show_in_rest' => true
		);
		register_meta( 'term', 'txm_image_id_meta', $arguments2 );

		$arguments3 = array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'single' => true,
			'description' => 'Custom meta image url field for all taxonomies',
			'show_in_rest' => true
		);
		register_meta( 'term', 'txm_image_url_meta', $arguments3 );

		$arguments4 = array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_text_field',
			'single' => true,
			'description' => 'Custom meta is feature field for all taxonomies',
			'show_in_rest' => true
		);
		register_meta( 'term', 'txm_is_feature_meta', $arguments4 );

	}


	/**
	 * Display table column name for all taxonomies
	 *
	 * @param  array $columns All default columns
	 * @return array
	 */
	function txm_manage_table_column($columns){
		unset($columns['description']);
		$columns['custom_field'] = __('Custom field','txm-meta');
		$columns['thumbnail'] = __('Thumbnail','txm-meta');
		$columns['is_feature'] = __('Is feature ?','txm-meta');
		return $columns;
	}

	/**
	 * Display table column value for all taxonomies
	 *
	 * @param  string $deprecated
	 * @param  string $column_name
	 * @param  string $term_id
	 * @return output
	 */
	function txm_manage_table_custom_fields($deprecated,$column_name,$term_id)
	{
		$metaValues = get_term_meta($term_id);
		if ($column_name == 'custom_field') {
			$custom_field = isset($metaValues['txm_custom_field'][0]) ? $metaValues['txm_custom_field'][0] : '';;
			echo $custom_field;
		}
		if ($column_name == 'thumbnail') {
			$image_url = isset($metaValues['txm_image_url_meta'][0]) ? '<img src="'.$metaValues['txm_image_url_meta'][0].'" width="100"/>' : '';;
			echo $image_url;
		}
		if ($column_name == 'is_feature') {

			$is_feature = get_term_meta($term_id, 'txm_is_feature_meta', true);
			echo ($is_feature==1) ? 'Yes' : 'No';

		}
	}


	/**
	 * Display meta for create
	 *
	 * @param  array $term
	 * @return Html output
	 */
	function txm_display_custom_fields($term){
		$label = __('Custom field','txm-meta');
		$hint = __('The custom field is an extra text field for this taxonomy. You can show it somewhere.','txm-meta');
		$html = <<<EOD
			<div class="form-field term-name-wrap">
				<label for="txm-custom-field">{$label}</label>
				<input name="txm-custom-field" id="txm-custom-field" type="text" value="" size="40">
				<p>{$hint}</p>
			</div>
		EOD;

		$labelImg = __('Feature image','txm-meta');
		$hintImg = __('The feature image section is for upload main/feature image of this taxonomies term. You can show it somewhere.','txm-meta');
		$btnImg = __('Upload image','txm-meta');
		$html .= <<<EOD
			<div class="form-field term-name-wrap">
				<label for="txm-image-id">{$labelImg}</label>
				<button class="button" id="upload_image">{$btnImg}</button>
				<input type="hidden" name="txm-image-id" id="txm-image-id" value="" size="40">
				<input type="hidden" name="txm-image-url" id="txm-image-url" value="" size="40">
				<p>{$hintImg}</p>
				<div class="txm-image-container"></div>
			</div>
		EOD;

		$labelFeature = __('Is Feature ?','txm-meta');
		$hintFeature = __('This section is for enable/disable feature taxonomies term. It can be useful.','txm-meta');
		$html .= <<<EOD
			<div class="form-field term-name-wrap">
				<label for="txm-feature-field">{$labelFeature}</label>
				<label class="txm-switch">
				  <input name="txm-feature-field" id="txm-feature-field" type="checkbox" value="1">
				  <span class="txm-slider round"></span>
				</label>
				<p>{$hintFeature}</p>
			</div>
		EOD;

		echo $html;
	}



	/**
	 * Insert meta field
	 *
	 * @param  $term_id
	 */		
	function txn_create_custom_field($term_id){
		$nonceAction = $_POST['_wpnonce_add-tag'];
		$nonceField = 'add-tag';
		if( wp_verify_nonce($nonceAction,$nonceField) ){
			$custom_field = sanitize_text_field($_POST['txm-custom-field']);
			$image_id = sanitize_text_field($_POST['txm-image-id']);
			$image_url = sanitize_text_field($_POST['txm-image-url']);
			$is_feature = sanitize_text_field($_POST['txm-feature-field']);
			update_term_meta( $term_id, 'txm_custom_field', $custom_field );
			update_term_meta( $term_id, 'txm_image_id_meta', $image_id );
			update_term_meta( $term_id, 'txm_image_url_meta', $image_url );
			update_term_meta( $term_id, 'txm_is_feature_meta', $is_feature );
		}
	}

	/**
	 * Display meta for edit
	 *
	 * @param  array $term
	 * @return Html output
	 */
	function txm_display_edit_custom_field($term){

		$label = __('Custom field','txm-meta');
		$hint = __('The custom field is an extra text field for this taxonomy. You can show it somewhere.','txm-meta');

		$custom_field = get_term_meta($term->term_id, 'txm_custom_field', true);
		$custom_field = esc_attr($custom_field);

		$html = <<<EOD

			<tr class="form-field term-name-wrap">
				<th scope="row"><label for="txm-custom-field">{$label}</label></th>
				<td>
					<input name="txm-custom-field" id="txm-custom-field" type="text" value="{$custom_field}" size="40">
					<p class="description">{$hint}</p>
				</td>
			</tr>

		EOD;

		$image_id = get_term_meta($term->term_id, 'txm_image_id_meta', true);
		$image_id = esc_attr($image_id);

		$image_url = get_term_meta($term->term_id, 'txm_image_url_meta', true);
		$image_url = esc_attr($image_url);

		$labelImg = __('Feature image','txm-meta');
		$hintImg = __('The feature image section is for upload main/feature image of this taxonomies term. You can show it somewhere.','txm-meta');
		$btnImg = __('Upload image','txm-meta');
		$html .= <<<EOD

			<tr class="form-field term-name-wrap">
				<th scope="row"><label for="txm-image-id">{$labelImg}</label></th>
				<td>
					<button class="button" id="upload_image">{$btnImg}</button>
					<input type="hidden" name="txm-image-id" id="txm-image-id" value="{$image_id}" size="40">
					<input type="hidden" name="txm-image-url" id="txm-image-url" value="{$image_url}" size="40">
					<p>{$hintImg}</p>
					<div class="txm-image-container"></div>
				</td>
			</tr>

		EOD;

		$is_feature = get_term_meta($term->term_id, 'txm_is_feature_meta', true);
		$is_feature = esc_attr($is_feature);
		$checked = ($is_feature==1) ? 'checked' : '';

		$labelFeature = __('Is Feature ?','txm-meta');
		$hintFeature = __('This section is for enable/disable feature taxonomies term. It can be useful.','txm-meta');
		$html .= <<<EOD

			<tr class="form-field term-name-wrap">
				<th scope="row"><label for="txm-feature-field">{$labelFeature}</label></th>
				<td>
					<label class="txm-switch">
					  <input name="txm-feature-field" id="txm-feature-field" type="checkbox" value="1" {$checked}>
					  <span class="txm-slider round"></span>
					</label>
					<p>{$hintFeature}</p>
				</td>
			</tr>

		EOD;

		echo $html;

	}

	/**
	 * Update meta field
	 *
	 * @param  array $term
	 */
	function txn_edit_custom_field($term_id){
		$nonceAction = $_POST['_wpnonce'];
		$nonceField = "update-tag_".$term_id;
		if( wp_verify_nonce($nonceAction,$nonceField) ){

			$custom_field = sanitize_text_field($_POST['txm-custom-field']);
			$image_id = sanitize_text_field($_POST['txm-image-id']);
			$image_url = sanitize_text_field($_POST['txm-image-url']);
			$is_feature = sanitize_text_field($_POST['txm-feature-field']);

			update_term_meta( $term_id, 'txm_custom_field', $custom_field );
			update_term_meta( $term_id, 'txm_image_id_meta', $image_id );
			update_term_meta( $term_id, 'txm_image_url_meta', $image_url );
			update_term_meta( $term_id, 'txm_is_feature_meta', $is_feature );

		}
	}
}
new Txm_taxonomy();


?>