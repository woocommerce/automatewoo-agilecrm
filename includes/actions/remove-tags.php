<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class AW_Action_AgileCRM_Remove_Tags
 */
class AW_Action_AgileCRM_Remove_Tags extends AW_Action_AgileCRM_Abstract {

	public $name = 'agilecrm_remove_tags';


	public function init() {
		$this->title = __( 'Remove Tags From Contact', 'automatewoo-agilecrm');
		parent::init();
	}


	public function load_fields() {
		$this->add_contact_email_field();
		$this->add_tags_field();
	}


	/**
	 * @return void
	 */
	public function run() {

		$email = AutomateWoo\Clean::email( $this->get_option( 'email', true ) );
		$tags = aw_clean( $this->get_option( 'tags', true ) );

		if ( empty( $tags ) || empty( $email ) || ! AW_AgileCRM()->api() )
			return;

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		$tags = array_map( 'trim', explode( ',', $tags ) );

		if ( $contact_id ) {
			// add tags
			$response = AW_AgileCRM()->api()->request( 'PUT', '/contacts/delete/tags', [
				'id' => $contact_id,
				"tags" => $tags
			]);
		}

	}

}
