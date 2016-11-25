<?php
/**
 * Set up and custom triggers or actions
 *
 * @class 		AW_AgileCRM_Workflows
 * @package		AutomateWoo/Add-ons/AgileCRM
 * @since		1.0.0
 */

class AW_AgileCRM_Workflows {


	public function __construct() {
		add_action( 'automatewoo_actions_loaded', [ $this, 'load_actions' ] );
	}


	public function load_actions() {

		include_once AW_AgileCRM()->path( '/includes/actions/abstract.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-contact.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-tags.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/remove-tags.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-note.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-task.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/create-deal.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/update-contact-field.php' );

		new AW_Action_AgileCRM_Add_Contact();
		new AW_Action_AgileCRM_Add_Tags();
		new AW_Action_AgileCRM_Remove_Tags();
		new AW_Action_AgileCRM_Add_Note();
		new AW_Action_AgileCRM_Add_Task();
		new AW_Action_AgileCRM_Update_Contact_Field();
		new AW_Action_AgileCRM_Create_Deal();
	}

}
