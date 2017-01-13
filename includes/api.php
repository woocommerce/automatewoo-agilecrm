<?php
/**
 * @class 		AW_AgileCRM_API
 * @package		AutomateWoo/Add-ons/AgileCRM
 * @since		2.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class AW_AgileCRM_API extends AW_Integration {

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
	 * @return AW_Remote_Request
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

		$request = new AW_Remote_Request( $url, $request_args );

		if ( $request->is_failed() ) {
			$this->log( $request->get_error_message() );
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
		return preg_replace('/\+[^@]*/i' , '', sanitize_email( strtolower( $email ) ) );
	}


	/**
	 * Return false if not found
	 *
	 * @param $email
	 * @return string|false
	 */
	function get_contact_id_by_email( $email ) {

		$email = $this->parse_email( $email );

		if ( $cache = $this->get_contact_id_cache( $email ) ) {
			if ( $cache == '204' ) return false;  // no matching contact
			return $cache;
		}

		$response = $this->request( 'GET' , "/contacts/search/email/$email" );

		// bail on failed request and don't set cache
		if ( ! $response->is_successful() )
			return false;

		$contact = $response->get_body();
		$id = isset( $contact['id'] ) ? $contact['id'] : false;

		$this->set_contact_id_cache( $email , $id );

		return $id;
	}


	/**
	 * @param $email
	 * @param $id
	 */
	function set_contact_id_cache( $email, $id ) {
		if ( ! $id ) $id = '204'; // no matching contact
		set_transient( 'aw_agilecrm_contact_id_' . md5( $this->parse_email( $email ) ), $id, DAY_IN_SECONDS * 7 );
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
	 * @param WC_Order|WP_User $order
	 * @param string $type shipping|billing
	 * @return array
	 */
	function get_address_data_from_order( $order, $type = 'billing' ) {
		$data = [];
		$countries = WC()->countries->get_countries();

		switch ( $type ) {
			case 'billing':
				$states = WC()->countries->get_states( $order->billing_country );
				$data['address'] = trim( $order->billing_address_1 . ' ' . $order->billing_address_2 );
				$data['city'] = $order->billing_city;
				$data['zip'] = $order->billing_postcode;
				$data['state'] = isset( $states[$order->billing_state] ) ? $states[$order->billing_state] : '';
				$data['country'] = isset( $countries[$order->billing_country] ) ? $countries[$order->billing_country] : '';
				break;

			case 'shipping':
				$states = WC()->countries->get_states( $order->shipping_country );
				$data['address'] = trim( $order->shipping_address_1 . ' ' . $order->shipping_address_2 );
				$data['city'] = $order->shipping_city;
				$data['zip'] = $order->shipping_postcode;
				$data['state'] = isset( $states[$order->shipping_state] ) ? $states[$order->shipping_state] : '';
				$data['country'] = isset( $countries[$order->shipping_country] ) ? $countries[$order->shipping_country] : '';
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
