<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_AgileCRM_Abstract
 */
abstract class Action_AgileCRM_Abstract extends Action {


	function init() {
		$this->group = __( 'AgileCRM', 'automatewoo-agilecrm' );
	}


	function check_requirements() {
		if ( ! function_exists('curl_init') ) {
			$this->warning( __('Server is missing CURL extension required to use the AgileCRM API.', 'automatewoo-agilecrm' ) );
		}
	}


	/**
	 * @return Fields\Text
	 */
	function add_contact_email_field() {
		$email = ( new Fields\Text() )
			->set_name( 'email' )
			->set_title( __( 'Contact Email', 'automatewoo-agilecrm' ) )
			->set_required()
			->set_description( __( 'You can use variables such as {{ customer.email }} here.', 'automatewoo-agilecrm' ) )
			->set_variable_validation();

		$this->add_field( $email );

		return $email;
	}


	/**
	 * @return Fields\Text
	 */
	function add_tags_field() {
		$tag = ( new Fields\Text() )
			->set_name('tags')
			->set_title( __( 'Tags', 'automatewoo-agilecrm' ) )
			->set_description( __( 'Add multiple tags separated by commas. Tags are case-sensitive. A tag should start with an alphanumeric character and cannot contain special characters other than underscore and space.', 'automatewoo-agilecrm' ) )
			->set_variable_validation();

		$this->add_field($tag);
		return $tag;
	}


	/**
	 * @param $string
	 * @return array
	 */
	function parse_tags_string( $string ) {
		$string = preg_replace( '/[^a-zA-Z_ ,0-9]/', ' ', $string );
		$tags = array_map( 'trim', explode( ',', Clean::string( $string ) ) );
		return $tags;
	}


}


