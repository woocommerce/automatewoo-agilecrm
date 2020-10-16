<?php
/**
 * Plugin Name: AutomateWoo - AgileCRM Add-on
 * Plugin URI: http://automatewoo.com
 * Description: AgileCRM Integration add-on for AutomateWoo.
 * Version: 1.4.5
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo-agilecrm
 *
 * GitHub Plugin URI: woocommerce/automatewoo-agilecrm
 * Primary Branch: trunk
 */


// Copyright (c) 2020 WooCommerce. All rights reserved.
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


/**
 * @class AW_AgileCRM_Plugin_Data
 */
class AW_AgileCRM_Plugin_Data {

	function __construct() {
		$this->id = 'automatewoo-agilecrm';
		$this->name = __( 'AutomateWoo - AgileCRM', 'automatewoo-agilecrm' );
		$this->version = '1.4.5';
		$this->file = __FILE__;
		$this->min_php_version = '5.4';
		$this->min_automatewoo_version = '4.3.0';
		$this->min_woocommerce_version = '3.0.0';
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
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
	}


	static function load() {
		self::check();
		if ( empty( self::$errors ) ) {
			include 'includes/automatewoo-agilecrm.php';
		}
	}


	static function check() {

		$inactive_text = '<strong>' . sprintf( __( '%s is inactive.', 'automatewoo-agilecrm' ), self::$data->name ) . '</strong>';

		if ( version_compare( phpversion(), self::$data->min_php_version, '<' ) ) {
			self::$errors[] = sprintf( __( '%s The plugin requires PHP version %s or newer.' , 'automatewoo-agilecrm' ), $inactive_text, self::$data->min_php_version );
		}

		if ( ! self::is_automatewoo_active() ) {
			self::$errors[] = sprintf( __( '%s The plugin requires AutomateWoo to be installed and activated.' , 'automatewoo-agilecrm' ), $inactive_text );
		}
		elseif ( ! self::is_automatewoo_version_ok() ) {
			self::$errors[] = sprintf(__( '%s The plugin requires AutomateWoo version %s or newer.', 'automatewoo-agilecrm' ), $inactive_text, self::$data->min_automatewoo_version );
		}
		elseif ( ! self::is_automatewoo_directory_name_ok() ) {
			self::$errors[] = sprintf(__( '%s AutomateWoo plugin directory name is not correct.', 'automatewoo-agilecrm' ), $inactive_text );
		}

		if ( ! self::is_woocommerce_version_ok() ) {
			self::$errors[] = sprintf(__( '%s The plugin requires WooCommerce version %s or newer.', 'automatewoo-agilecrm' ), $inactive_text, self::$data->min_woocommerce_version );
		}
	}


	static function load_textdomain() {
		load_plugin_textdomain( 'automatewoo-agilecrm', false, "automatewoo-agilecrm/languages" );
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
		echo '<div class="notice notice-error"><p>';
		echo implode( '<br>', self::$errors );
		echo '</p></div>';
	}


}

AW_AgileCRM_Loader::init( new AW_AgileCRM_Plugin_Data() );
