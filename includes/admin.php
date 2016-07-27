<?php
/**
 * @class AW_AgileCRM_Admin
 */

class AW_AgileCRM_Admin
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		add_filter( 'automatewoo/settings/tabs', [ $this, 'settings_tab' ] );
	}



	/**
	 * @param $tabs
	 * @return array
	 */
	public function settings_tab( $tabs )
	{
		$tabs[] = AW_AgileCRM()->path( '/includes/settings-tab.php' );
		return $tabs;
	}


	/**
	 * @param $view
	 * @param array $args
	 */
	public function get_view( $view, $args = [] )
	{
		if ( $args && is_array( $args ) )
			extract( $args );

		$path = AW_Referrals()->path( '/includes/admin/views/' . $view );

		if ( file_exists( $path ) )
			include( $path );
	}


}
