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
	}

	/**
	 * Filter display settitngs.
	 * @since 0.1
	 * @param array $args The display args
	 * @return array $args Filtered display args
	 */
	public function filter_all_display_settings( $args ) {
		
		$contact_settings = PT_CV_Functions::settings_values_by_prefix( PT_CV_PREFIX . 'civi-contact-', true );

		$args['contact-settings'] = $contact_settings;
		
		foreach ( $contact_settings as $name => $value ) {
			$args['fields'][] = $name;
		}

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

		if ( $post->post_type == 'civicrm_contact' )
			return $html = '<div class="col-md-12 pt-cv-ctf-column"><div class="pt-cv-custom-fields pt-cv-ctf-post_field_1"><div class="pt-cv-ctf-value">' . $post->$field_name . '</div></div></div>';

		return $html;
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
			'label' => [ 'text' => __( 'Contact settings', 'content-views-civicrm' ) ],
			'extra_setting' => [
				'params' => [
					'wrap-class' => PT_CV_Html::html_group_class(),
					'wrap-id' => PT_CV_Html::html_group_id( 'contact-settings' )
				]
			],
			'dependence' => [ 'content-type', [ 'civicrm_contact' ] ],
			'params' => [
				[
					'type' => 'group',
					'params' => $this->contact_fields_group_options()
					/*[
						[
							'label' => [ 'text' => __( 'Core fields', 'content-views-civicrm' ) ],
							'extra_setting'	 => [
								'params' => [
									'group-class' => PT_CV_PREFIX . 'field-setting' . ' ' . PT_CV_PREFIX . 'contact-field-settings',
									'wrap-class' => PT_CV_Html::html_group_class() . ' ' . PT_CV_PREFIX . 'contact-field-settings'
								]
							],
							'params' => [
								[
									'type' => 'group',
									'params' => $this->get_contact_fields()
								]
							]
						]
					]*/
				]
			]
		];
	}

	/**
	 * Contact field group.
	 * @since 0.1
	 * @return array $options
	 */
	public function contact_fields_group_options() {
		return [
			[
				'label' => [
					'text' => __( 'Contact fields', 'content-views-civicrm' )
				],
				'extra_setting'	 => [
					'params' => [
						'group-class'	 => PT_CV_PREFIX . 'field-setting' . ' ' . PT_CV_PREFIX . 'contact-fields-settings',
						'wrap-class'	 => PT_CV_Html::html_group_class() . ' ' . PT_CV_PREFIX . 'contact-fields-settings'
					]
				],
				'params' => [
					[
						'type' => 'group',
						'params' => $this->get_contact_fields_options()
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
		
		$fields = \Civi\Api4\Contact::getFields()->execute();

		return array_reduce( ( array ) $fields, function( $fields, $field ) {

			$fields[] = [
				'label'	=> [ 'text' => '' ],
				'extra_setting' => [
					'params' => [ 'width' => 12 ]
				],
				'params' => [
					[
						'type'		 => 'checkbox',
						'name'		 => 'civi-contact-' . $field['name'],
						'options'	 => PT_CV_Values::yes_no( 'yes', __( $field['title'], 'content-views-civicrm' ) )
					]
				]
			];

			return $fields;

		}, [] );
	}

}