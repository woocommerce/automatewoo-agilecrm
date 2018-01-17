<?php
/**
 * Set up and custom triggers or actions
 *
 * @class AW_AgileCRM_Workflows
 */

class AW_AgileCRM_Workflows {


	function __construct() {
		add_filter( 'automatewoo/actions', [ $this, 'actions' ] );
	}


	/**
	 * @param array $actions
	 * @return array
	 */
	function actions( $actions ) {

		include_once AW_AgileCRM()->path( '/includes/actions/abstract.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-contact.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-tags.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/remove-tags.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-note.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/add-task.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/create-deal.php' );
		include_once AW_AgileCRM()->path( '/includes/actions/update-contact-field.php' );

		$actions['agilecrm_add_contact'] = 'AutomateWoo\Action_AgileCRM_Add_Contact';
		$actions['agilecrm_add_tags'] = 'AutomateWoo\Action_AgileCRM_Add_Tags';
		$actions['agilecrm_remove_tags'] = 'AutomateWoo\Action_AgileCRM_Remove_Tags';
		$actions['agilecrm_add_note'] = 'AutomateWoo\Action_AgileCRM_Add_Note';
		$actions['agilecrm_add_task'] = 'AutomateWoo\Action_AgileCRM_Add_Task';
		$actions['agilecrm_update_contact_field'] = 'AutomateWoo\Action_AgileCRM_Update_Contact_Field';
		$actions['agilecrm_create_deal'] = 'AutomateWoo\Action_AgileCRM_Create_Deal';

		return $actions;
	}


}
