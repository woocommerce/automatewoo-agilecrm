<?php
/**
 * @class       AW_Action_AgileCRM_Add_Contact
 * @package     AutomateWoo/Addons/AgileCRM
 * @since       1.0.0
 */

class AW_Action_AgileCRM_Add_Contact extends AW_Action_AgileCRM_Abstract
{
	public $name = 'agilecrm_add_contact';

	/**
	 * Init
	 */
	public function init()
	{
		$this->title = __( 'Create New Contact', 'automatewoo-agilecrm' );
		parent::init();
	}


	public function load_fields()
	{
		$first_name = ( new AW_Field_Text_Input() )
			->set_name( 'first_name' )
			->set_title( __( 'First Name', 'automatewoo-agilecrm' ) );

		$last_name = ( new AW_Field_Text_Input() )
			->set_name( 'last_name' )
			->set_title( __( 'Last Name', 'automatewoo-agilecrm' ) );

		$company = ( new AW_Field_Text_Input() )
			->set_name( 'company' )
			->set_title( __( 'Company', 'automatewoo-agilecrm' ) );

		$title = ( new AW_Field_Text_Input() )
			->set_name( 'title' )
			->set_title( __( 'Title', 'automatewoo-agilecrm' ) );

		$star_value = ( new AW_Field_Number_Input() )
			->set_name('star_value')
			->set_title( __( 'Star Value', 'automatewoo-agilecrm' ) )
			->set_min(0)
			->set_max(5);

		$lead_score = ( new AW_Field_Number_Input() )
			->set_name('lead_score')
			->set_title( __( 'Lead Score', 'automatewoo-agilecrm' ) )
			->set_min(0);



		$this->add_contact_email_field();
		$this->add_field( $first_name );
		$this->add_field( $last_name );
		$this->add_field( $company );
		$this->add_field( $title );
		$this->add_field( $star_value );
		$this->add_field( $lead_score );
		$this->add_tags_field();

	}


	/**
	 * @return void
	 */
	public function run()
	{
		$email = aw_clean_email( $this->get_option( 'email', true ) );
		$first_name = aw_clean( $this->get_option( 'first_name' ) );
		$last_name = aw_clean( $this->get_option( 'last_name' ) );
		$company = aw_clean( $this->get_option( 'company' ) );
		$title = aw_clean( $this->get_option( 'title' ) );
		$star_value = absint( $this->get_option( 'star_value' ) );
		$lead_score = absint( $this->get_option( 'lead_score' ) );
		$tags = aw_clean( $this->get_option( 'tags', true ) );

		if ( empty( $email ) || ! AW_AgileCRM()->api() )
			return;

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		if ( $contact_id ) return; // contact already exists

		$contact = [
			'properties' => []
		];

		$contact['properties'][] = [
			'type' =>  'SYSTEM',
			'name' => 'email',
			"value" => $email
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


		if ( $star_value ) $contact['star_value'] = $star_value;
		if ( $lead_score ) $contact['lead_score'] = $lead_score;

		if ( $tags )
		{
			$contact['tags'] = array_map( 'trim', explode( ',', $tags ) );
		}

		$response = AW_AgileCRM()->api()->request( 'POST', '/contacts', $contact );

	}

}
