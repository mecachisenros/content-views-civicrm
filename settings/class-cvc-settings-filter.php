<?php

/**
 * Content_Views_CiviCRM_Settings_Filter class. 
 */
class Content_Views_CiviCRM_Settings_Filter {

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
		// add contact post type
		add_filter( PT_CV_PREFIX_ . 'post_types_list', [ $this, 'filter_post_types_list' ] );
		// contact filters
		add_filter( PT_CV_PREFIX_ . 'custom_filters', [ $this, 'contact_filters' ] );
	}

	/**
	 * Filter post type list.
	 *
	 * Adds Contact post type to the Filter settings.
	 * @since 0.1
	 * @param array $types Post types list
	 * @return array $types Filtered post types list
	 */
	public function filter_post_types_list( $types ) {
		$types['civicrm_contact'] = __( 'Contact', 'content-views-civicrm' );
		return $types;
	}

	public function contact_filters() {
		return [
			'label' => [ 'text' => __( 'Contact filters', 'content-views-civicrm' ) ],
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
							'label' => [ 'text' => __( 'Contact types', 'content-views-civicrm' ) ],
							'params' => [
								[
									'type' => 'select',
									'name' => 'contact_type',
									'options' => $this->contact_type_options(),
									'class' => 'select2',
									'multiple' => 1,
									'std' => 'Organization',
									'desc' => __( 'Filter by contact types.', 'content-views-civicrm' )
								]
							]
						],
						// contact sub type
						// [
						// 	'label' => [ 'text' => __( 'Contact sub types', 'content-views-civicrm' ) ],
						// 	'params' => [
						// 		[
						// 			'type' => 'select',
						// 			'name' => 'contact_sub_type',
						// 			'options' => $this->contact_sub_type_options(),
						// 			'class' => 'select2',
						// 			'multiple' => 1,
						// 			'std' => 'Organization',
						// 			'desc' => __( 'Filter by contact sub types.', 'content-views-civicrm' )
						// 		]
						// 	]
						// ],
						// groups include
						[
							'label' => [ 'text' => __( 'Groups include', 'content-views-civicrm' ) ],
							'params' => [
								[
									'type' => 'select',
									'name' => 'group_include',
									'options' => $this->group_options(),
									'class' => 'select2',
									'multiple' => 1,
									'std' => '',
									'desc' => __( 'Filter contacts only in those groups.', 'content-views-civicrm' )
								]
							]
						],
						// groups exclude
						[
							'label' => [ 'text' => __( 'Groups exclude', 'content-views-civicrm' ) ],
							'params' => [
								[
									'type' => 'select',
									'name' => 'group_exclude',
									'options' => $this->group_options(),
									'class' => 'select2',
									'multiple' => 1,
									'std' => '',
									'desc' => __( 'Exclude contacts in those groups.', 'content-views-civicrm' )
								]
							]
						]
					]
				]
			]
		];
	}

	/**
	 * Get contact types options.
	 * @since 0.1
	 * @return array $types Contact types
	 */
	public function contact_type_options() {
		$contact_types_result = \Civi\Api4\ContactType::get()
			->addWhere( 'is_active', '=', 1 )
			// ->addWhere( 'parent_id', 'IS NULL' )
			->setLimit( 0 )
  			->execute();

		$types = array_reduce( ( array ) $contact_types_result, function( $types, $type ) {
			$types[$type['name']] = $type['name'];
			return $types;
		}, [] );

		return $types;
	}

	/**
	 * Get contact types options.
	 * @since 0.1
	 * @return array $types Contact types
	 */
	public function contact_sub_type_options() {
		$contact_types_result = \Civi\Api4\ContactType::get()
			->addWhere( 'is_active', '=', 1 )
			->addWhere( 'parent_id', 'IS NOT NULL' )
			->setLimit( 0 )
  			->execute();

		$types = array_reduce( ( array ) $contact_types_result, function( $types, $type ) {
			$types[$type['name']] = $type['name'];
			return $types;
		}, [] );

		return $types;
	}

	/**
	 * Get groups options.
	 * @since 0.1
	 * @return array $types Groups
	 */
	public function group_options() {
		$groups_result = \Civi\Api4\Group::get()
			->addWhere( 'is_active', '=', 1 )
			->setLimit( 0 )
  			->execute();

		$groups = array_reduce( ( array ) $groups_result, function( $groups, $group ) {
			$groups[$group['id']] = $group['name'];
			return $groups;
		}, [] );

		return $groups;
	}
}