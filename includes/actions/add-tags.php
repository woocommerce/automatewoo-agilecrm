<?php
/**
 * @class       AW_Action_AgileCRM_Add_Tags
 * @package     AutomateWoo/Addons/AgileCRM
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class AW_Action_AgileCRM_Add_Tags extends AW_Action_AgileCRM_Abstract {

	public $name = 'agilecrm_add_tags';

	public function init() {
		$this->title = __( 'Add Tags To Contact', 'automatewoo-agilecrm' );
		parent::init();
	}


	public function load_fields() {

//		$create_user = ( new AW_Field_Checkbox() )
//			->set_title(__( "Create Contact If Missing", 'automatewoo-agilecrm' ) )
//			->set_name( 'create_missing_contact' );

		$this->add_contact_email_field();
		$this->add_tags_field();
//		$this->add_field($create_user);
	}


	/**
	 * @return void
	 */
	public function run() {

		$email = AutomateWoo\Clean::email( $this->get_option( 'email', true ) );
		$tags = aw_clean( $this->get_option( 'tags', true ) );
//		$create_missing_contact = $this->get_option('create_missing_contact');

		if ( empty( $tags ) || empty( $email ) || ! AW_AgileCRM()->api() )
			return;

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		$tags = array_map( 'trim', explode( ',', $tags ) );

		if ( $contact_id ) {
			// add tags
			$response = AW_AgileCRM()->api()->request( 'PUT', '/contacts/edit/tags', [
				'id' => $contact_id,
				"tags" => $tags
			]);
		}
		else {

//			if ( $create_missing_contact )
//			{
//				// create contact from email
//				$contact = [
//					"tags" => $tags,
//					'properties' => [
//						[
//							"type" => "SYSTEM",
//							"name" => "email",
//							"subtype" => "work",
//							"value" => $email
//						]
//					]
//				];
//
//
//				// fill in any user data
//				$user = $this->workflow->get_data_item('user');
//
//
//				// first name is apparently required
//				$contact['properties'][] = [
//					"type" => "SYSTEM",
//					"name" => "first_name",
//					"value" => $user && $user->first_name ? $user->first_name : 'Guest'
//				];
//
//				if ( $user )
//				{
//					if ( $user->last_name )
//					{
//						$contact['properties'][] = [
//							"type" => "SYSTEM",
//							"name" => "last_name",
//							"value" => $user->last_name
//						];
//					}
//				}
//
//
//				$response = AW_AgileCRM()->api()->request( 'POST', '/contacts', $contact );
//
//				if ( $response->get_response_code() == 200 )
//				{
//					$response_body = $response->get_body();
//
//					if ( $response_body['id'] )
//					{
//						AW_AgileCRM()->api()->set_contact_id_cache( $email, $response_body['id'] );
//					}
//				}
//			}
		}

	}

}
