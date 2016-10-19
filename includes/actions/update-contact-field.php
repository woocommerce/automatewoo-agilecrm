<?php
/**
 * @class       AW_Action_AgileCRM_Update_Contact_Field
 * @package     AutomateWoo/Addons/AgileCRM
 * @since       1.0.0
 */

class AW_Action_AgileCRM_Update_Contact_Field extends AW_Action_AgileCRM_Abstract
{
	public $name = 'agilecrm_update_contact_field';

	/**
	 * Init
	 */
	public function init()
	{
		$this->title = __( 'Update Contact Custom Field', 'automatewoo-agilecrm' );
		parent::init();
	}


	public function load_fields()
	{
		$field_name = ( new AW_Field_Text_Input() )
			->set_name('field_name')
			->set_title( __( 'Custom Field Name', 'automatewoo-agilecrm' ) )
			->set_required();

		$field_value = ( new AW_Field_Text_Input() )
			->set_name('field_value')
			->set_title( __( 'Custom Field Value', 'automatewoo-agilecrm' ) );

		$this->add_contact_email_field();
		$this->add_field( $field_name );
		$this->add_field( $field_value );
	}


	/**
	 * @return void
	 */
	public function run()
	{
		$email = aw_clean_email( $this->get_option( 'email', true ) );
		$field_name = aw_clean( $this->get_option( 'field_name' ) );
		$field_value = aw_clean( $this->get_option( 'field_value', true ) );

		if ( empty( $field_name ) || empty( $email ) || ! AW_AgileCRM()->api() )
			return;

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		if ( $contact_id )
		{
			// add tags
			$response = AW_AgileCRM()->api()->request( 'PUT', '/contacts/edit-properties', [
				'id' => $contact_id,
				'properties' => [
					[
						'type' => 'CUSTOM',
						'name' => $field_name,
						'value' => html_entity_decode( $field_value )
					]
				],
			]);
		}

	}

}
