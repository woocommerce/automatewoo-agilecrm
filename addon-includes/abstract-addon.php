<?php
/**
 * @class 		AW_Abstract_Addon
 * @package		AutomateWoo/Addon Includes
 */

if ( ! class_exists('AW_Abstract_Addon') ):


	abstract class AW_Abstract_Addon
	{
		/** @var string */
		public $id;

		/** @var string */
		public $name;

		/** @var string */
		public $version;

		/** @var string */
		public $plugin_slug;

		/** @var string */
		public $plugin_main_file;

		/** @var string */
		public $plugin_basename;

		/** @var string */
		public $plugin_path;

		/** @var string */
		public $required_automatewoo_version = '2.4.12';

		/** @var string */
		public $required_woocommerce_version = '2.4';

		/** @var array */
		public $db_updates = [];


		/**
		 * Method to init the add on
		 */
		abstract function init();

		/**
		 * Required method to return options class
		 * @return AW_Options_API
		 */
		abstract function options();


		/**
		 * Constructor
		 */
		function __construct()
		{
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );
			add_action( 'automatewoo_init_addons', array( $this, 'maybe_init' ) );
		}


		/**
		 * @param string $end
		 * @return string
		 */
		function url( $end = '' )
		{
			return untrailingslashit( plugin_dir_url( $this->plugin_basename ) ) . $end;
		}


		/**
		 * @param string $end
		 * @return string
		 */
		function path( $end = '' )
		{
			return untrailingslashit( $this->plugin_path ) . $end;
		}


		/**
		 * @return bool
		 */
		function is_automatewoo_active()
		{
			$active_plugins = (array) get_option( 'active_plugins', array() );

			return in_array( 'automatewoo/automatewoo.php', $active_plugins ) || array_key_exists( 'automatewoo/automatewoo.php', $active_plugins );
		}


		/**
		 * @return bool
		 */
		function is_automatewoo_version_ok()
		{
			return version_compare( AW()->version, $this->required_automatewoo_version, '>=' );
		}


		/**
		 * @return bool
		 */
		function is_woocommerce_version_ok()
		{
			if ( ! function_exists( 'WC' ) ) return false;

			if ( ! $this->required_woocommerce_version ) return true;

			return version_compare( WC()->version, $this->required_woocommerce_version, '>=' );
		}


		/**
		 * Adds an admin notice if required
		 */
		function admin_notices()
		{
			if ( ! $this->is_automatewoo_active() )
			{
				$message = sprintf(__( '<strong>%s</strong> requires AutomateWoo to be installed and activated.', 'automatewoo' ), $this->name );
				echo '<div class="notice notice-warning"><p>' . $message . '</p></div>' . "\n";
			}
			else if ( ! $this->is_automatewoo_version_ok() )
			{
				$message = sprintf(__( '<strong>%s</strong> requires AutomateWoo version %s or later. Please update to the latest version.', 'automatewoo' ), $this->name, $this->required_automatewoo_version );
				echo '<div class="notice notice-warning"><p>' . $message . '</p></div>' . "\n";
			}

			if ( ! $this->is_woocommerce_version_ok() )
			{
				$message = sprintf(__( '<strong>%s</strong> requires WooCommerce version %s or later.', 'automatewoo' ), $this->name, $this->required_woocommerce_version );
				echo '<div class="notice notice-warning"><p>' . $message . '</p></div>' . "\n";
			}
		}


		/**
		 * @return bool
		 */
		function is_database_upgrade_available()
		{
			if ( $this->options()->version == $this->version )
				return false;

			return $this->options()->version && version_compare( $this->options()->version, max( $this->db_updates ), '<' );
		}


		/**
		 * Handle updates
		 */
		function do_database_update()
		{
			@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

			foreach ( $this->db_updates as $update )
			{
				if ( version_compare( $this->options()->version, $update, '<' ) )
				{
					include( $this->path( "/includes/updates/$update.php" ) );
				}
			}

			$this->update_database_version();
		}


		/**
		 * Update version to current
		 */
		function update_database_version()
		{
			update_option( $this->options()->prefix . 'version', $this->version, true );
		}


		/**
		 *
		 */
		function maybe_init()
		{
			if ( ! $this->is_automatewoo_active() || ! $this->is_automatewoo_version_ok() || ! $this->is_woocommerce_version_ok() ) return;

			AW()->addons()->register( $this );

			if ( AW()->licenses->is_active( $this->id ) )
			{
				$this->init();
			}
		}


		/**
		 * Runs when the license for the add-on is activated
		 */
		function activate()
		{
			flush_rewrite_rules();
		}


		/**
		 * @return string
		 */
		function admin_start_url()
		{
			return '';
		}


	}

endif;
