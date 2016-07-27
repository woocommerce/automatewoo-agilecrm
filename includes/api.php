<?php
/**
 * @class 		AW_AgileCRM_API
 * @package		AutomateWoo/Add-ons/AgileCRM
 * @since		2.3
 */

class AW_AgileCRM_API extends AW_Integration
{
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
	function __construct( $api_domain, $api_email, $api_key )
	{
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
	function request( $method, $endpoint, $args = array() )
	{
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

		switch ( $method )
		{
			case 'GET':
				$url = add_query_arg( $args, $url );
				break;

			default:
				$request_args['body'] = json_encode( $args );
				break;
		}

		$request = new AW_Remote_Request( $url, $request_args );

		if ( $request->is_failed() )
		{
			$this->log( $request->get_error_message() );
		}
		elseif ( ! $request->is_http_success_code() )
		{
			$this->log(
				$request->get_response_code() . ' ' . $request->get_response_message()
				. '. Method: ' . $method
				. '. Endpoint: ' . $endpoint
				. '. Response body: ' . print_r( $request->get_body(), true ) );
		}

		return $request;
	}


	/**
	 * Return false if not found
	 *
	 * @param $email
	 * @return string|false
	 */
	function get_contact_id_by_email( $email )
	{
		$email = sanitize_email( strtolower( $email ) );

		if ( $cache = $this->get_contact_id_cache( $email ) )
		{
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
	function set_contact_id_cache( $email, $id )
	{
		if ( ! $id ) $id = '204'; // no matching contact

		set_transient( 'aw_agilecrm_contact_id_' . md5( $email ), $id, DAY_IN_SECONDS * 30 );
	}


	/**
	 * @param $email
	 * @return string|false
	 */
	function get_contact_id_cache( $email )
	{
		return get_transient( 'aw_agilecrm_contact_id_' . md5( $email ) );
	}


}
