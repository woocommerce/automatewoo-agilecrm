<?php

/**
 * Class AW_Action_AgileCRM_Abstract
 */

abstract class AW_Action_AgileCRM_Abstract extends AW_Action
{
	/** @var string  */
	public $group = 'AgileCRM';


	function check_requirements()
	{
		if ( ! function_exists('curl_init') )
		{
			$this->warning( __('Server is missing CURL extension required to use the AgileCRM API.', 'automatewoo-agilecrm' ) );
		}
	}


	function add_contact_email_field()
	{
		$email = ( new AW_Field_Text_Input() )
			->set_name( 'email' )
			->set_title( __( 'Contact Email', 'automatewoo-agilecrm' ) )
			->set_required()
			->set_description( __( 'You can use variables such as user.email or guest.email here.', 'automatewoo-agilecrm' ) );

		$this->add_field( $email );
	}


	function add_tags_field()
	{
		$tag = ( new AW_Field_Text_Input() )
			->set_name('tags')
			->set_title( __( 'Tags', 'automatewoo-agilecrm' ) )
			->set_description( __( 'Add multiple tags separated by commas. Please note that tags are case-sensitive.', 'automatewoo-agilecrm' ) )
			->set_required();

		$this->add_field($tag);
	}
}


