<?php

/**
 * Content_Views_CiviCRM_Settings_Display class.
 */
class Content_Views_CiviCRM_Settings_Display {

	/**
	 * Plugin instance reference.
	 * @since 0.1
	 * @var object Reference to plugin instance
	 */
	protected $cvc;

	/**
	 * Contact fields.
	 *
	 * @since 0.1
	 * @var array
	 */
	public $contact_fields = [];

	/**
	 * Constructor.
	 * @since 0.1
	 * @param object $cvc Reference to plugin instance
	 */
	public function __construct( $cvc ) {
		$this->cvc = $cvc;
		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 * @since 0.1
	 */
	public function register_hooks() {
		// filter display settings
		add_filter( PT_CV_PREFIX_ . 'display_settings', [ $this, 'filter_display_settings' ] );

		add_filter( PT_CV_PREFIX_ . 'all_display_settings', [ $this, 'filter_all_display_settings' ] );

		add_filter( PT_CV_PREFIX_ . 'field_item_html', [ $this, 'contact_fields_html' ], 5, 3 );

		add_filter( PT_CV_PREFIX_ . 'dargs_others', function( $dargs, $post_idx ) {

			return $dargs;
		}, 20, 2 );
	}

	/**
	 * Filter display settitngs.
	 * @since 0.1
	 * @param array $args The display args
	 * @return array $args Filtered display args
	 */
	public function filter_all_display_settings( $args ) {

		$contact_fields = PT_CV_Functions::settings_values_by_prefix( PT_CV_PREFIX . 'contact_fields', true );

		$args['fields'] = array_merge( $args['fields'], reset( $contact_fields ) );

		return $args;
	}

	/**
	 * Render contact fields html.
	 * @param string $html
	 * @param string $field_name
	 * @param WP_Post $post WP_Post
	 * @return string $html
	 */
	public function contact_fields_html( $html, $field_name, $post ) {

		if ( $post->post_type != 'civicrm_contact' ) return $html;

		if ( empty( $this->contact_fields ) )
			$this->contact_fields = $this->get_contact_fields_options();

		return $html = "<div class='col-md-12 pt-cv-ctf-column'><div class='pt-cv-custom-fields pt-cv-ctf-post_field_1'><div class='pt-cv-ctf-value'><strong>{$this->contact_fields[$field_name]}</strong>: {$post->$field_name}</div></div></div>";

	}


	/**
	 * Filter display settings.
	 * @since 0.1
	 * @param array $options Display settings field options
	 * @return array $options Filtered display settings field options
	 */
	public function filter_display_settings( $options ) {
		// set field dependences
		return $this->set_field_settings_group_dependences( $options );
	}

	/**
	 * Set Field settings group dependences.
	 * @since 0.1
	 * @param array $options Display settings field options
	 * @return array $options Filtered display settings field options
	 */
	public function set_field_settings_group_dependences( $options ) {

		// all post but civicrm_contact post type
		$all_post_types_but_contact = array_diff( array_keys( PT_CV_Values::post_types() ), ['civicrm_contact'] );

		return array_reduce( $options, function( $options, $group ) use ( $all_post_types_but_contact ) {

			if ( isset( $group['label']['text'] ) && $group['label']['text'] == 'Fields settings' ) {
				// set dependence to all posts
				// needed to toggle off field settings for contact post type
				$options[] = $group['dependence'] = [ 'content-type', $all_post_types_but_contact ];

				// add our own contact field settings
				$options[] = $this->contact_fields_display_settings();
			}

			$options[] = $group;

			return $options;

		}, [] );

	}

	/**
	 * Contact fields display settings options.
	 * @since 0.1
	 * @return array $options Contact field options
	 */
	public function contact_fields_display_settings() {

		return [
			'label' => [ 'text' => __( 'Contact display settings', 'content-views-civicrm' ) ],
			'extra_setting'	 => [
				'params' => [
					'wrap-class' => PT_CV_Html::html_panel_group_class(),
					'wrap-id'	 => PT_CV_Html::html_panel_group_id( PT_CV_Functions::string_random() )
				]
			],
			'dependence' => [ 'content-type', [ 'civicrm_contact' ] ],
			'params' => [
				[
					'type' => 'group',
					'params' => [
						// contact type
						[
							'label' => [ 'text' => __( 'Contact fields', 'content-views-civicrm' ) ],
							'params' => [
								[
									'type' => 'select',
									'name' => 'contact_fields',
									'options' => $this->get_contact_fields_options(),
									'class' => 'select2',
									'multiple' => 1
								]
							]
						]
					]
				]
			]
		];

	}

	/**
	 * Contact fields options.
	 * @since 0.1
	 * @return array $options
	 */
	public function get_contact_fields_options() {

		return array_reduce( $this->get_fields_for( 'Contact' ), function( $fields, $field ) {

			$fields[$field['name']] = $field['title'];

			return $fields;

		}, [] );

	}

	/**
	 * Retrieve fields for an entity.
	 *
	 * @since 0.1
	 * @return array $options
	 */
	public function get_fields_for( $entity ) {

		return $this->cvc->api->call_values( $entity, 'getfields', [ 'action' => 'get' ] );

	}

}
