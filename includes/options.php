<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class AW_AgileCRM_Options
 *
 * @property string $api_domain
 * @property string $api_email
 * @property string $api_key
 */
class AW_AgileCRM_Options extends AutomateWoo\Options_API {

	/** @var string */
	public $prefix = 'aw_agilecrm_';


	public function __construct() {
		$this->defaults = [

		];
	}
}

