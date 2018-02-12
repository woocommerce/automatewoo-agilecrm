<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AutomateWoo\Addon' ) ) {
	include WP_PLUGIN_DIR . '/automatewoo/includes/abstracts/addon.php';
}

class AW_AgileCRM_Addon extends AutomateWoo\Addon {

	/** @var AW_AgileCRM_Options */
	private $options;

	/** @var AW_AgileCRM_Admin */
	public $admin;

	/** @var AutomateWoo\AgileCRM\API */
	private $api;


	/**
	 * @param AW_Referrals_Plugin_Data $plugin_data
	 */
	public function __construct( $plugin_data ) {
		parent::__construct( $plugin_data );
	}


	/**
	 * Initiate
	 */
	public function init() {

		$this->includes();

		new AW_AgileCRM_Workflows();

		if ( is_admin() ) {
			$this->admin = new AW_AgileCRM_Admin();
		}

		do_action( 'automatewoo/agilecrm/after_init' );
	}


	/**
	 * Includes
	 */
	public function includes() {
		
		include_once $this->path( '/includes/workflows.php' );

		if ( is_admin() ) {
			include_once $this->path( '/includes/admin.php' );
		}
	}



	/**
	 * @return AW_AgileCRM_Options
	 */
	public function options() {
		if ( ! isset( $this->options ) ) {
			include_once $this->path( '/includes/options.php' );
			$this->options = new AW_AgileCRM_Options();
		}

		return $this->options;
	}


	/**
	 * @return AutomateWoo\AgileCRM\API|false
	 */
	public function api() {
		if ( ! isset( $this->api ) ) {
			include_once $this->path( '/includes/api.php' );

			$api_domain = AutomateWoo\Clean::string( $this->options()->api_domain );
			$api_email = AutomateWoo\Clean::string( $this->options()->api_email );
			$api_key = AutomateWoo\Clean::string( $this->options()->api_key );

			$api_domain = str_replace( [ 'http://', 'https://' ], '', $api_domain );

			if ( $api_domain && $api_email && $api_key ) {
				$this->api = new AutomateWoo\AgileCRM\API( $api_domain, $api_email, $api_key );
			}
			else {
				$this->api = false;
			}
		}

		return $this->api;
	}


	/** @var AW_AgileCRM_Addon */
	protected static $_instance;


}


/**
 * @return AW_AgileCRM_Addon
 */
function AW_AgileCRM() {
	return AW_AgileCRM_Addon::instance( new AW_AgileCRM_Plugin_Data() );
}
AW_AgileCRM();
