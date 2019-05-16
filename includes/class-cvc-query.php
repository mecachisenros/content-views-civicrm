<?php

/**
 * Content_Views_CiviCRM_Query class.
 */
class Content_Views_CiviCRM_Query {

	/**
	 * Plugin instance reference.
	 * @since 0.1
	 * @var object Reference to plugin instance
	 */
	protected $cvc;

	/**
	 * Constructor.
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
		// alter query params
		add_filter( PT_CV_PREFIX_ . 'query_parameters', [ $this, 'alter_query_parameters' ] );
		// filter query params
		add_filter( PT_CV_PREFIX_ . 'query_params', [ $this, 'filter_query_params' ] );
	}

	/**
	 * Alter query parameters before filtering them.
	 * @since 0.1
	 * @param array $args WP_Query parameters
	 * @return array $args WP_Query parameters
	 */
	public function alter_query_parameters( $args ) {
		// content view globals
		global $pt_cv_glb, $pt_cv_id;

		if ( is_array( $pt_cv_glb[$pt_cv_id] ) ) {
			// view settings
			$view_settings = $pt_cv_glb[$pt_cv_id]['view_settings'];

			$api_params = [];

			if ( ! empty( $view_settings[PT_CV_PREFIX . 'contact_type'] ) )
				$api_params['contact_type'] = [ 'IN' => $view_settings[ PT_CV_PREFIX . 'contact_type'] ];

			if ( ! empty( $view_settings[PT_CV_PREFIX . 'contact_sub_type'] ) )
				$api_params['contact_sub_type'] = [ 'IN' => $view_settings[PT_CV_PREFIX . 'contact_sub_type'] ];

			if ( ! empty( $view_settings[PT_CV_PREFIX . 'group_include'] ) )
				$api_params['group_include'] = $view_settings[PT_CV_PREFIX . 'group_include'];

			if ( ! empty( $view_settings[PT_CV_PREFIX . 'group_exclude'] ) )
				$api_params['group_exclude'] = $view_settings[PT_CV_PREFIX . 'group_exclude'];

			if ( ! empty( $view_settings[PT_CV_PREFIX . 'limit'] ) )
				$api_params['options'] = [ 'limit' => $view_settings[PT_CV_PREFIX . 'limit'] ];

			$args['civicrm_api_params'] = $api_params;

		}


		return $args;
	}

	/**
	 * Filters query parameters before they WP_Query is instantiated.
	 * @since 0.1
	 * @param array $args WP_Query parameters
	 * @return array $args WP_Query parameters
	 */
	public function filter_query_params( $args ) {
		if ( $args['post_type'] == 'civicrm_contact' )
			// bypass query
			$this->bypass_query( $args );
		return $args;
	}


	/**
	 * Bypasses the WP_Query.
	 *
	 * When quering a Contact post type bypasses the WP_Query
	 * and use Civi's API to retrieve Contacts.
	 * @since 0.1
	 * @uses 'posts_pre_query'
	 * @param array $args Query args to instantiate WP_Query
	 */
	public function bypass_query( $args ) {
		// bypass query
		add_filter( 'posts_pre_query', function( $posts, $class ) use ( $args ) {

			$contacts = $this->cvc->api->call_values( 'Contact', 'get', $args['civicrm_api_params'] );

			// mock WP_Posts contacts
			foreach ( $contacts as $contact ) {
				$post = new WP_Post( (object) [] );
				$post->ID = $contact['id'];
				$post->post_title = $contact['sort_name'];
				$post->post_type = 'civicrm_contact';
				$post->filter = 'raw'; // set to raw to bypass sanitization

				// clean object
				foreach ( $post as $prop => $value ) {
					if ( ! in_array( $prop, [ 'ID', 'post_title', 'post_type', 'filter' ] ) )
						unset( $post->$prop );
				}
				// add rest of contact properties
				foreach ( $contact as $field => $value ) {
					if ( ! in_array( $field, [ 'hash' ] ) )
						$post->$field = $value;
				}

				// build array
				$posts[] = $post;

			}

			return $posts;

		}, 10, 2 );
	}
}
