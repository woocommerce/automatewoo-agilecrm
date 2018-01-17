<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_AgileCRM_Add_Task
 */
class Action_AgileCRM_Add_Task extends Action_AgileCRM_Abstract {


	public function init() {
		$this->title = __( 'Add Task To Contact', 'automatewoo-agilecrm' );
		$this->description = __( 'Please note you must first create the contact in AgileCRM before assigning any tasks to them.', 'automatewoo-agilecrm' );
		parent::init();
	}


	public function load_fields() {

		$name = ( new Fields\Text() )
			->set_name('subject')
			->set_title( __( 'Task Name', 'automatewoo-agilecrm' ) )
			->set_required();

		$owner = ( new Fields\Select( false ) )
			->set_name( 'owner' )
			->set_title( __( 'Task Owner', 'automatewoo-agilecrm' ) )
			->set_options( AW_AgileCRM()->api()->get_users() )
			->set_required();

		$type = ( new Fields\Select( false ) )
			->set_name('type')
			->set_title( __( 'Task Type', 'automatewoo-agilecrm' ) )
			->set_options([
				'CALL' => 'Call',
				'EMAIL' => 'Email',
				'FOLLOW_UP' => 'Follow Up',
				'MEETING' => 'Meeting',
				'MILESTONE' => 'Milestone',
				'SEND' => 'Send',
				'TWEET' => 'Tweet',
				'OTHER' => 'Other'
			])
			->set_required();

		$priority = ( new Fields\Select( false ) )
			->set_name('priority')
			->set_title( __( 'Priority', 'automatewoo-agilecrm' ) )
			->set_default( 'NORMAL' )
			->set_options([
				'HIGH' => 'High',
				'NORMAL' => 'Normal',
				'LOW' => 'Low'
			])
			->set_required();

		$due = ( new Fields\Text() )
			->set_name('due')
			->set_title( __( 'Due', 'automatewoo-agilecrm' ) )
			->set_placeholder('e.g. {{ shop.current_datetime | modify : +1 day }}')
			->set_required();

		$description = ( new Fields\Text_Area() )
			->set_name('description')
			->set_title( __( 'Description', 'automatewoo-agilecrm' ) )
			->set_rows( 3 );

		$this->add_contact_email_field();
		$this->add_field( $name );
		$this->add_field( $owner );
		$this->add_field( $type );
		$this->add_field( $priority );
		$this->add_field( $due );
		$this->add_field( $description );
	}


	public function run() {
		$email = Clean::email( $this->get_option( 'email', true ) );
		$subject = Clean::string( $this->get_option( 'subject', true ) );
		$owner = Clean::string( $this->get_option( 'owner' ) );
		$type = Clean::string( $this->get_option( 'type' ) );
		$priority = Clean::string( $this->get_option( 'priority' ) );
		$due = Clean::string( $this->get_option( 'due', true ) );
		$description = Clean::textarea( $this->get_option( 'description', true ) );

		if ( empty( $subject ) || empty( $email ) || ! AW_AgileCRM()->api() ) {
			return;
		}

		$contact_id = AW_AgileCRM()->api()->get_contact_id_by_email( $email );

		if ( ! $contact_id ) {
			return;
		}

		// convert to gmt timestamp
		if ( ! $due = strtotime( get_gmt_from_date( $due ) ) ) {
			$due = time();
		}

		$data = [
			'contacts' => [ $contact_id ],
			'subject' => $subject,
			'type' => $type,
			'priority' => $priority,
			'due' => $due,
			'taskDescription' => $description,
		];

		if ( $owner ) {
			$data['owner_id'] = $owner;
		}

		$response = AW_AgileCRM()->api()->request( 'POST', '/tasks', $data );
	}

}
