<?php

namespace AutomateWoo\AgileCRM;

use AutomateWoo\Clean;
use AutomateWoo\Integration;
use AutomateWoo\Remote_Request;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class API
 * @since 2.3
 */
class API extends Integration {

	/** @var string */
	public $integration_id = 'agilecrm';

	/** @var string */
	private $api_email;

	/** @var string */
	private $api_key;

	/** @var string  */
	private $api_root = 'https://<domain>.agilecrm.com/dev/api';


	/**
	 * @param $api_domain
	 * @param $api_email
	 * @param $api_key
	 */
	function __construct( $api_domain, $api_email, $api_key ) {
		$this->api_email = $api_email;
		$this->api_key = $api_key;
		$this->api_root = str_replace( '<domain>', $api_domain, $this->api_root );
	}


	/**
	 * Automatically logs errors
	 *
	 * @param $method
	 * @param $endpoint
	 * @param $args
	 *
	 * @return Remote_Request
	 */
	function request( $method, $endpoint, $args = [] ) {

		$request_args = [
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode(  $this->api_email . ':' . $this->api_key ),
				'Accept' => ' application/json',
				'Content-Type' => 'application/json'
			],
			'timeout' => 15,
			'method' => $method,
			'sslverify' => false
		];

		$url = $this->api_root . $endpoint;

		switch ( $method ) {
			case 'GET':
				$url = add_query_arg( $args, $url );
				break;

			default:
				$request_args['body'] = json_encode( $args );
				break;
		}

		$request = new Remote_Request( $url, $request_args );

		if ( $request->is_http_error() ) {
			$this->log( $request->get_http_error_message() );
		}
		elseif ( ! $request->is_http_success_code() ) {
			$this->log(
				$request->get_response_code() . ' ' . $request->get_response_message()
				. '. Method: ' . $method
				. '. Endpoint: ' . $endpoint
				. '. Response body: ' . print_r( $request->get_body(), true ) );
		}

		return $request;
	}


	/**
	 * Remove '+' part of emails for agileCRM
	 * @param $email
	 * @return string
	 */
	function parse_email( $email ) {
		return preg_replace('/\+[^@]*/i' , '', Clean::email( $email ) );
	}


	/**
	 * Return false if not found
	 *
	 * @param $email
	 * @return int|false
	 */
	function get_contact_id_by_email( $email ) {

		$email = $this->parse_email( $email );

		if ( $cache = $this->get_contact_id_cache( $email ) ) {
			if ( $cache == '204' ) {
				return false; // no matching contact
			}
			return (int) $cache;
		}

		$response = $this->request( 'GET' , "/contacts/search/email/$email" );

		// bail on failed request and don't set cache
		if ( ! $response->is_successful() ) {
			return false;
		}

		$contact = $response->get_body();

		if ( empty( $contact['id'] ) ) {
			$this->set_contact_id_cache( $email );
			return false;
		}

		$id = (int) $contact['id'];

		$this->set_contact_id_cache( $email , $id );

		return $id;
	}


	/**
	 * @param string $email
	 * @param bool|int $id
	 */
	function set_contact_id_cache( $email, $id = false ) {
		if ( ! $id ) {
			$id = '204'; // no matching contact
		}
		set_transient( 'aw_agilecrm_contact_id_' . md5( $this->parse_email( $email ) ), $id, HOUR_IN_SECONDS * 2 );
	}


	/**
	 * @param $email
	 * @return string|false
	 */
	function get_contact_id_cache( $email ) {
		return get_transient( 'aw_agilecrm_contact_id_' . md5( $this->parse_email( $email ) ) );
	}


	/**
	 * @param $email
	 */
	function clear_contact_id_cache( $email ) {
		delete_transient( 'aw_agilecrm_contact_id_' . md5( $this->parse_email( $email ) ) );
	}


	/**
	 * @param \AutomateWoo\Data_Layer $data_layer
	 * @param string $type shipping|billing
	 * @return array
	 */
	function get_address_from_workflow_data( $data_layer, $type = 'billing' ) {
		$data = [];

		switch ( $type ) {
			case 'billing':
				$data['address'] = trim( $data_layer->get_customer_address_1() . ' ' . $data_layer->get_customer_address_2() );
				$data['city']    = $data_layer->get_customer_city();
				$data['zip']     = $data_layer->get_customer_postcode();
				$data['state']   = aw_get_state_name( $data_layer->get_customer_country(), $data_layer->get_customer_state() );
				$data['country'] = aw_get_country_name( $data_layer->get_customer_country() );
				break;

			case 'shipping':
				$order = $data_layer->get_order();

				if ( $order ) {
					$data['address'] = trim( $order->get_shipping_address_1() . ' ' . $order->get_shipping_address_2() );
					$data['city']    = $order->get_shipping_city();
					$data['zip']     = $order->get_shipping_postcode();
					$data['state']   = aw_get_state_name( $order->get_shipping_country(), $order->get_shipping_state() );
					$data['country'] = aw_get_country_name( $order->get_shipping_country() );
				}
				else {
					$customer = $data_layer->get_customer();

					if ( $customer ) {
						$user = $customer->get_user();

						if ( $user ) {
							$data['address'] = trim( $user->shipping_address_1 . ' ' . $user->shipping_address_2 );
							$data['city']    = $user->shipping_city;
							$data['zip']     = $user->shipping_postcode;
							$data['state']   = aw_get_state_name( $user->shipping_country, $user->shipping_state );
							$data['country'] = aw_get_country_name( $user->shipping_country );
						}
					}
				}

				break;
		}
		return $data;
	}


	/**
	 * @return array
	 */
	function get_milestones() {
		if ( $cache = get_transient( 'aw_agilecrm_milestones' ) )
			return $cache;

		$response = $this->request( 'GET' , '/milestone/pipelines' );

		if ( ! $response->is_successful() )
			return [];

		$body = $response->get_body();
		$milestones = [];

		if ( is_array( $body ) ) foreach ( $body as $track ) {
			if ( isset( $track['milestones'] ) ) {
				$milestones = array_merge( $milestones, explode( ',', $track['milestones'] ) );
			}
		}

		set_transient( 'aw_agilecrm_milestones', $milestones, MINUTE_IN_SECONDS * 5 );

		return $milestones;
	}


	/**
	 * @since 1.2.5
	 * @return array
	 */
	function get_users() {

		if ( $cache = get_transient( 'aw_agilecrm_users' ) )
			return $cache;

		$response = $this->request( 'GET' , '/users' );

		if ( ! $response->is_successful() )
			return [];

		$body = $response->get_body();
		$users = [];

		if ( is_array( $body ) ) foreach ( $body as $item ) {
			if ( isset( $item['id'] ) ) {
				$users[ $item['id'] ] = $item[ 'name' ] . ' <' . $item[ 'email' ] . '>';
			}
		}

		set_transient( 'aw_agilecrm_users', $users, MINUTE_IN_SECONDS * 5 );

		return $users;
	}

}
