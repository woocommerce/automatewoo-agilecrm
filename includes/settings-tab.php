<?php
/**
 * @class 		AW_AgileCRM_Settings_Tab
 */

class AW_AgileCRM_Settings_Tab extends AW_Admin_Settings_Tab_Abstract
{
	/** @var bool */
	public $show_tab_title = false;

	/** @var string  */
	public $prefix = 'aw_agilecrm_';


	public function __construct()
	{
		$this->id = 'agilecrm';
		$this->name = __( 'AgileCRM', 'automatewoo-agilecrm' );
	}


	/**
	 *
	 */
	public function load_settings()
	{
		if ( ! empty( $this->settings ) )
			return;

		$this->section_start( 'api', __( 'AgileCRM API Details', 'automatewoo-agilecrm' ) );

		$this->add_setting( 'api_domain', [
			'title' => __( 'Account Domain', 'automatewoo-agilecrm' ),
			'type' => 'text',
			'desc' => '.agilecrm.com',
			'css' => 'width: 150px'
		]);

		$this->add_setting( 'api_email', [
			'title' => __( 'Account Email', 'automatewoo-agilecrm' ),
			'type' => 'text',
			'tooltip' => __( 'The email address you use to sign in to AgileCRM.', 'automatewoo-agilecrm' )
		]);

		$this->add_setting( 'api_key', [
			'title' => __( 'API Key', 'automatewoo-agilecrm' ),
			'type' => 'text',
			'tooltip' => __( 'Locate your AgileCRM API Key from Admin Settings -> API & Analytics -> REST API.', 'automatewoo-agilecrm' )
		]);

		$this->section_end( 'api' );


	}


	public function get_settings()
	{
		$this->load_settings();
		return $this->settings;
	}


	/**
	 * @param $id
	 * @param $args
	 * @return array
	 */
	protected function add_setting( $id, $args )
	{
		$setting = [
			'id' => $this->prefix . $id,
			'autoload' => false
		];

		if ( isset( AW_AgileCRM()->options()->defaults[ $id ] ) )
		{
			$setting['default'] = AW_AgileCRM()->options()->defaults[ $id ];
		}

		$setting = array_merge( $setting, $args );
		$this->settings[] = $setting;
	}


}

return new AW_AgileCRM_Settings_Tab();
