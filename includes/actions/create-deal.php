<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_AgileCRM_Create_Deal
 * @since 1.2
 */
class Action_AgileCRM_Create_Deal extends Action_AgileCRM_Abstract {


	function init() {
		$this->title = __('Create Deal', 'automatewoo-agilecrm');
		parent::init();
	}


	function load_fields() {

		$name = ( new Fields\Text() )
			->set_name('name')
			->set_title( __( 'Name', 'automatewoo-agilecrm' ) )
			->set_required();

		$value = ( new Fields\Text() )
			->set_name('value')
			->set_title( __( 'Value', 'automatewoo-agilecrm' ) )
			->set_required();

		$probability = ( new Fields\Number() )
			->set_name('probability')
			->set_title( __( 'Probability (%)', 'automatewoo-agilecrm' ) )
			->set_min( 0 )
			->set_max( 100 )
			->set_required();

		$milestone = ( new Fields\Select( false ) )
			->set_name( 'milestone' )
			->set_title( __( 'Milestone', 'automatewoo-agilecrm' ) )
			->set_options( array_combine(
				AW_AgileCRM()->api()->get_milestones(),
				AW_AgileCRM()->api()->get_milestones()
			) )
			->set_required();

		$close_date = ( new Fields\Text() )
			->set_name('close_date')
			->set_title( __( 'Close Date', 'automatewoo-agilecrm' ) )
			->set_description('e.g. {{ shop.current_datetime | modify : +1 week }}');

		$description = ( new Fields\Text_Area() )
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


	function run() {

		$name = Clean::string( $this->get_option( 'name', true ) );
		$value = Clean::string( $this->get_option( 'value', true ) );
		$probability = absint( $this->get_option( 'probability', true ) );
		$milestone = Clean::string( $this->get_option( 'milestone' ) );
		$contact_email = Clean::email( $this->get_option( 'email', true ) );
		$close_date = Clean::string( $this->get_option( 'close_date', true ) );
		$description = Clean::textarea( $this->get_option( 'description', true ) );

		if ( empty( $name ) || empty( $value ) || empty( $milestone ) || empty( $name ) || ! AW_AgileCRM()->api() )
			return;

		$data = [
			'name' => $name,
			'expected_value' => aw_price_to_float( $value ),
			'probability' => $probability,
			'milestone' => $milestone
		];

		if ( $close_date ) {
			$data['close_date'] = strtotime( get_gmt_from_date( $close_date ) );
		}

		if ( $contact_email ) {
			if ( $contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $contact_email ) ) {
				$data['contact_ids'] = [ $contact_id ];
			}
		}

		if ( $description ) {
			$data['description'] = $description;
		}

		AW_AgileCRM()->api()->request( 'POST', '/opportunity', $data );
	}

}
