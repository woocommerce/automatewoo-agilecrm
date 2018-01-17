<?php
/**
 * Plugin Name: AutomateWoo - AgileCRM Add-on
 * Plugin URI: http://automatewoo.com
 * Description: AgileCRM Integration add-on for AutomateWoo.
 * Version: 1.4.0
 * Author: AutomateWoo
 * Author URI: http://automatewoo.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo-agilecrm
 */


// Copyright (c) 2016 AutomateWoo. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************


if ( ! defined( 'ABSPATH' ) ) exit;


load_plugin_textdomain( 'automatewoo-agilecrm', false, "automatewoo-agilecrm/languages" );


/**
 * @class AW_AgileCRM_Plugin_Data
 */
class AW_AgileCRM_Plugin_Data {

	function __construct() {
		$this->id = 'automatewoo-agilecrm';
		$this->name = __( 'AutomateWoo - AgileCRM Add-on', 'automatewoo-agilecrm' );
		$this->version = '1.4.0';
		$this->file = __FILE__;
		$this->min_php_version = '5.4';
		$this->min_automatewoo_version = '3.3.0';
		$this->min_woocommerce_version = '2.6';
	}
}



/**
 * @class AW_AgileCRM_Loader
 */
class AW_AgileCRM_Loader {

	/** @var AW_AgileCRM_Plugin_Data */
	static $data;

	static $errors = array();


	/**
	 * @param AW_AgileCRM_Plugin_Data $data
	 */
	static function init( $data ) {
		self::$data = $data;

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'plugins_loaded', array( __CLASS__, 'load' ) );
	}


	static function load() {
		self::check();
		if ( empty( self::$errors ) ) {
			include 'includes/automatewoo-agilecrm.php';
		}
	}



	static function check() {

		if ( version_compare( phpversion(), self::$data->min_php_version, '<' ) ) {
			self::$errors[] = sprintf( __( '<strong>%s</strong> requires PHP version %s+.' , 'automatewoo-agilecrm' ), self::$data->name, self::$data->min_php_version );
		}

		if ( ! self::is_automatewoo_active() ) {
			self::$errors[] = sprintf( __( '<strong>%s</strong> requires AutomateWoo to be installed and activated.' , 'automatewoo-agilecrm' ), self::$data->name );
		}
		elseif ( ! self::is_automatewoo_version_ok() ) {
			self::$errors[] = sprintf(__( '<strong>%s</strong> requires AutomateWoo version %s or later. Please update to the latest version.', 'automatewoo-agilecrm' ), self::$data->name, self::$data->min_automatewoo_version );
		}
		elseif ( ! self::is_automatewoo_directory_name_ok() ) {
			self::$errors[] = sprintf(__( '<strong>%s</strong> - AutomateWoo plugin directory name is not correct.', 'automatewoo-agilecrm' ), self::$data->name );
		}

		if ( ! self::is_woocommerce_version_ok() ) {
			self::$errors[] = sprintf(__( '<strong>%s</strong> requires WooCommerce version %s or later.', 'automatewoo-agilecrm' ), self::$data->name, self::$data->min_woocommerce_version );
		}
	}


	/**
	 * @return bool
	 */
	static function is_automatewoo_active() {
		return function_exists( 'AW' );
	}


	/**
	 * @return bool
	 */
	static function is_automatewoo_version_ok() {
		if ( ! function_exists( 'AW' ) ) return false;
		return version_compare( AW()->version, self::$data->min_automatewoo_version, '>=' );
	}


	/**
	 * @return bool
	 */
	static function is_woocommerce_version_ok() {
		if ( ! function_exists( 'WC' ) ) return false;
		if ( ! self::$data->min_woocommerce_version ) return true;
		return version_compare( WC()->version, self::$data->min_woocommerce_version, '>=' );
	}


	/**
	 * @return bool
	 */
	static function is_automatewoo_directory_name_ok() {
		$active_plugins = (array) get_option( 'active_plugins', [] );
		return in_array( 'automatewoo/automatewoo.php', $active_plugins ) || array_key_exists( 'automatewoo/automatewoo.php', $active_plugins );
	}


	static function admin_notices() {
		if ( empty( self::$errors ) ) return;
		echo '<div class="notice notice-warning"><p>';
		echo implode( '<br>', self::$errors );
		echo '</p></div>';
	}


}

AW_AgileCRM_Loader::init( new AW_AgileCRM_Plugin_Data() );
