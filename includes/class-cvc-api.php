<?php

/**
 * Content Views CiviCRM Api class.
 *
 * @since 0.1
 */

if ( ! class_exists( 'Content_Views_CiviCRM_Api' ) ) {

	class Content_Views_CiviCRM_Api {

		/**
		 * Call CiviCRM API.
		 *
		 * @since  0.1
		 * @param string $entity
		 * @param string $action
		 * @param array $params
		 * @return array $result
		 */
		public function call( $entity, $action, $params ) {

			try {

				return civicrm_api3( $entity, $action, $params );

			} catch ( CiviCRM_API3_Exception $e ) {

				return WP_Error( 'CVC CiviCRM Api error', $e->getMessage(), $params );

			}

		}

		/**
		 * Get CiviCRM API values.
		 *
		 * @since  0.1
		 * @param string $entity
		 * @param string $action
		 * @param array $params
		 * @return array $result
		 */
		public function call_values( $entity, $action, $params ) {

			return $this->call( $entity, $action, $params )['values'];

		}

	}

}

