<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_AgileCRM_Add_Contact
 */
class Action_AgileCRM_Add_Contact extends Action_AgileCRM_Abstract {


	public function init() {
		$this->title = __( 'Create / Update Contact', 'automatewoo-agilecrm' );
		$this->description = __( 'This trigger can be used to create or update contacts in AgileCRM. If an existing contact is found by email then an update will occur otherwise a new contact will be created. When updating a contact any fields left blank will not be updated e.g. if you only want to update the address just select an address and enter an email, all other fields can be left blank.', 'automatewoo-agilecrm' );
		parent::init();
	}


	public function load_fields() {

		$first_name = ( new Fields\Text() )
			->set_name( 'first_name' )
			->set_title( __( 'First Name', 'automatewoo-agilecrm' ) );

		$last_name = ( new Fields\Text() )
			->set_name( 'last_name' )
			->set_title( __( 'Last Name', 'automatewoo-agilecrm' ) );

		$company = ( new Fields\Text() )
			->set_name( 'company' )
			->set_title( __( 'Company', 'automatewoo-agilecrm' ) );

		$title = ( new Fields\Text() )
			->set_name( 'title' )
			->set_title( __( 'Title', 'automatewoo-agilecrm' ) );

		$address_choices = [
			'billing' => __( 'Billing Address', 'automatewoo-agilecrm' ),
			'shipping' => __( 'Shipping Address', 'automatewoo-agilecrm' ),
		];

		$address = ( new Fields\Select() )
			->set_name( 'address' )
			->set_options( $address_choices )
			->set_title( __( 'Address', 'automatewoo-agilecrm' ) );

		$star_value = ( new Fields\Number() )
			->set_name('star_value')
			->set_title( __( 'Star Value', 'automatewoo-agilecrm' ) )
			->set_min(0)
			->set_max(5);

		$lead_score = ( new Fields\Number() )
			->set_name('lead_score')
			->set_title( __( 'Lead Score', 'automatewoo-agilecrm' ) )
			->set_min(0);


		$this->add_contact_email_field();
		$this->add_field( $first_name );
		$this->add_field( $last_name );
		$this->add_field( $company );
		$this->add_field( $title );
		$this->add_field( $address );
		$this->add_field( $star_value );
		$this->add_field( $lead_score );
		$this->add_tags_field();
	}


	function run() {
		$email = Clean::email( $this->get_option( 'email', true ) );
		$first_name = Clean::string( $this->get_option( 'first_name', true ) );
		$last_name = Clean::string( $this->get_option( 'last_name', true ) );
		$company = Clean::string( $this->get_option( 'company', true ) );
		$title = Clean::string( $this->get_option( 'title', true ) );
		$address = Clean::string( $this->get_option( 'address' ) );
		$star_value = Clean::string( $this->get_option( 'star_value', true ) );
		$lead_score = Clean::string( $this->get_option( 'lead_score', true ) );
		$tags = Clean::string( $this->get_option( 'tags', true ) );

		if ( empty( $email ) || ! AW_AgileCRM()->api() ) {
			return;
		}

		$contact = [
			'properties' => []
		];
		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		if ( $contact_id ) {
			// update a contact
			$contact['id'] = $contact_id;
			$method = 'PUT';
			$endpoint = '/contacts/edit-properties';
		}
		else {
			$method = 'POST';
			$endpoint = '/contacts';
			AW_AgileCRM()->api()->clear_contact_id_cache( $email ); // clear cache because this contact is about to exist
		}

		$contact['properties'][] = [
			'type' =>  'SYSTEM',
			'name' => 'email',
			"value" => AW_AgileCRM()->api()->parse_email( $email )
		];

		if ( $first_name ) $contact['properties'][] = [
			'type' =>  'SYSTEM',
			'name' => 'first_name',
			"value" => $first_name
		];

		if ( $last_name ) $contact['properties'][] = [
			'type' =>  'SYSTEM',
			'name' => 'last_name',
			"value" => $last_name
		];

		if ( $company ) $contact['properties'][] = [
			'type' =>  'SYSTEM',
			'name' => 'company',
			"value" => $company
		];

		if ( $title ) $contact['properties'][] = [
			'type' =>  'SYSTEM',
			'name' => 'title',
			"value" => $title
		];


		if ( $address ) {
			$address_data = false;
			$order = $this->workflow->get_data_item( 'order' );
			$user = $this->workflow->get_data_item( 'user' );

			if ( $order || $user instanceof \WP_User ) {
				$object = $order ? $order : $user;

				switch ( $address ) {
					case 'billing':
						$address_data = AW_AgileCRM()->api()->get_address_data_from_order( $object, 'billing' );
						break;

					case 'shipping':
						$address_data = AW_AgileCRM()->api()->get_address_data_from_order( $object, 'shipping' );
						break;
				}

				if ( $address_data ) {
					$contact['properties'][] = [
						'type' =>  'SYSTEM',
						'name' => 'address',
						"value" => json_encode( $address_data )
					];
				}
			}
		}


		if ( $star_value ) $contact['star_value'] = $star_value;
		if ( $lead_score ) $contact['lead_score'] = $lead_score;

		if ( $tags ) {
			$contact['tags'] = array_map( 'trim', explode( ',', $tags ) );
		}

		$response = AW_AgileCRM()->api()->request( $method, $endpoint, $contact );

	}

}
