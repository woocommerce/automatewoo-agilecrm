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
		$this->name = __( 'AgileCRM', 'automatewoo-referrals' );
	}


	/**
	 *
	 */
	public function load_settings()
	{
		if ( ! empty( $this->settings ) )
			return;


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

return new AW_Referrals_Settings_Tab();
