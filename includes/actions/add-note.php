<?php
/**
 * @class       AW_Action_AgileCRM_Add_Note
 * @package     AutomateWoo/Addons/AgileCRM
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class AW_Action_AgileCRM_Add_Note extends AW_Action_AgileCRM_Abstract
{
	public $name = 'agilecrm_add_note';

	/**
	 * Init
	 */
	public function init()
	{
		$this->title = __('Add Note To Contact', 'automatewoo-agilecrm');
		parent::init();
	}


	public function load_fields()
	{
		$subject = ( new AW_Field_Text_Input() )
			->set_name('subject')
			->set_title( __( 'Note Subject', 'automatewoo-agilecrm' ) )
			->set_required();

		$description = ( new AW_Field_Text_Area() )
			->set_name('description')
			->set_title( __( 'Note Description', 'automatewoo-agilecrm' ) )
			->set_rows( 3 );

		$this->add_contact_email_field();
		$this->add_field( $subject );
		$this->add_field( $description );

	}


	/**
	 * @return void
	 */
	public function run()
	{
		$email = aw_clean_email( $this->get_option( 'email', true ) );
		$subject = aw_clean( $this->get_option( 'subject', true ) );
		$description = aw_clean( $this->get_option( 'description', true ) );

		if ( empty( $subject ) || empty( $email ) || ! AW_AgileCRM()->api() )
			return;

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		if ( $contact_id )
		{
			// add tags
			$response = AW_AgileCRM()->api()->request( 'POST', '/notes', [
				'subject' => $subject,
				'description' => $description,
				'contact_ids' => [ $contact_id ],
			]);
		}

	}

}
