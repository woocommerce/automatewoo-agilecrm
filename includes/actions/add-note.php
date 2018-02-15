<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_AgileCRM_Add_Note
 */
class Action_AgileCRM_Add_Note extends Action_AgileCRM_Abstract {


	public function init() {
		$this->title = __('Add Note To Contact', 'automatewoo-agilecrm');
		parent::init();
	}


	public function load_fields() {

		$subject = ( new Fields\Text() )
			->set_name('subject')
			->set_title( __( 'Note Subject', 'automatewoo-agilecrm' ) )
			->set_required()
			->set_variable_validation();

		$description = ( new Fields\Text_Area() )
			->set_name('description')
			->set_title( __( 'Note Description', 'automatewoo-agilecrm' ) )
			->set_rows( 3 )
			->set_variable_validation();

		$this->add_contact_email_field();
		$this->add_field( $subject );
		$this->add_field( $description );
	}


	function run() {
		$email = Clean::email( $this->get_option( 'email', true ) );
		$subject = Clean::string( $this->get_option( 'subject', true ) );
		$description = Clean::textarea( $this->get_option( 'description', true ) );

		if ( empty( $subject ) || empty( $email ) || ! AW_AgileCRM()->api() ) {
			return;
		}

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		if ( $contact_id ) {
			// add tags
			$response = AW_AgileCRM()->api()->request( 'POST', '/notes', [
				'subject' => $subject,
				'description' => $description,
				'contact_ids' => [ $contact_id ],
			]);
		}
	}

}
