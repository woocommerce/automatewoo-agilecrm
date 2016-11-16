<?php
/**
 * @class       AW_Action_AgileCRM_Create_Deal
 * @package     AutomateWoo/Addons/AgileCRM
 * @since       1.2
 */

class AW_Action_AgileCRM_Create_Deal extends AW_Action_AgileCRM_Abstract
{
	public $name = 'agilecrm_create_deal';

	/**
	 * Init
	 */
	public function init()
	{
		$this->title = __('Create Deal', 'automatewoo-agilecrm');
		parent::init();
	}


	public function load_fields()
	{
		$name = ( new AW_Field_Text_Input() )
			->set_name('name')
			->set_title( __( 'Name', 'automatewoo-agilecrm' ) )
			->set_required();

		$value = ( new AW_Field_Text_Input() )
			->set_name('value')
			->set_title( __( 'Value', 'automatewoo-agilecrm' ) )
			->set_required();

		$probability = ( new AW_Field_Number_Input() )
			->set_name('probability')
			->set_title( __( 'Probability (%)', 'automatewoo-agilecrm' ) )
			->set_min( 0 )
			->set_max( 100 )
			->set_required();

		$milestone = ( new AW_Field_Select( false ) )
			->set_name( 'milestone' )
			->set_title( __( 'Milestone', 'automatewoo-agilecrm' ) )
			->set_options( array_combine(
				AW_AgileCRM()->api()->get_milestones(),
				AW_AgileCRM()->api()->get_milestones()
			) )
			->set_required();

		$close_date = ( new AW_Field_Text_Input() )
			->set_name('close_date')
			->set_title( __( 'Close Date', 'automatewoo-agilecrm' ) )
			->set_description('e.g. {{ shop.current_datetime | modify : +1 week }}');

		$description = ( new AW_Field_Text_Area() )
			->set_name( 'description' )
			->set_title( __( 'Description', 'automatewoo-agilecrm' ) )
			->set_rows( 3 );

		$this->add_field( $name );
		$this->add_field( $value );
		$this->add_field( $probability );
		$this->add_field( $milestone );
		$this->add_contact_email_field()
			->set_required( false )
			->set_description( __( "Please note that you must create the contact before you can assign a deal to them.", 'automatewoo') );
		$this->add_field( $close_date );
		$this->add_field( $description );
	}


	/**
	 * @return void
	 */
	public function run()
	{
		$name = aw_clean( $this->get_option( 'name', true ) );
		$value = aw_clean( $this->get_option( 'value', true ) );
		$probability = absint( $this->get_option( 'probability', true ) );
		$milestone = aw_clean( $this->get_option( 'milestone' ) );
		$contact_email = aw_clean_email( $this->get_option( 'email', true ) );
		$close_date = aw_clean( $this->get_option( 'close_date', true ) );
		$description = aw_clean( $this->get_option( 'description', true ) );

		if ( empty( $name ) || empty( $value ) || empty( $milestone ) || empty( $name ) || ! AW_AgileCRM()->api() )
			return;

		$data = [
			'name' => $name,
			'expected_value' => aw_price_to_float( $value ),
			'probability' => $probability,
			'milestone' => $milestone
		];

		if ( $close_date )
		{
			$data['close_date'] = strtotime( get_gmt_from_date( $close_date ) );
		}

		if ( $contact_email )
		{
			if ( $contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $contact_email ) )
			{
				$data['contact_ids'] = [ $contact_id ];
			}
		}

		if ( $description )
		{
			$data['description'] = $description;
		}

		AW_AgileCRM()->api()->request( 'POST', '/opportunity', $data );
	}

}
