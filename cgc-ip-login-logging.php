<?php
/*
 * Plugin Name: CGC IP Login Logging
 */


class CGC_IP_Login_Logging {

	function __construct() {

		add_action( 'wp_login', array( $this, 'log_ip' ), 10, 2 );
		//add_action( 'wp_footer', array( $this, 'test' ) );

	}

	static function get_log() {
		return get_user_meta( get_current_user_id(), '_cgc_login_ips', true );
	}

	function log_ip( $user_login, $user ) {


		$log = get_user_meta( $user->ID, '_cgc_login_ips', true );
		if( empty( $log ) )
			$log = array();

		$ip  = $this->get_ip();

		if( sizeof( $log ) > 10 ) {

			array_shift( $log );

		}

		$api = wp_remote_get( 'http://api.hostip.info/get_json.php?ip=' . $ip );
		if( ! is_wp_error( $api ) ) {
			$response = wp_remote_retrieve_body( $api );
		}

		// remove the first IP

		$log[] = array(
			'geo'  => json_decode( $response ),
			'time' => current_time( 'timestamp' )
		);

		update_user_meta( $user->ID, '_cgc_login_ips', $log );

	}


	function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}


	function test() {
		$log = get_user_meta( get_current_user_id(), '_cgc_login_ips', true );
		echo '<pre>';
		print_r( $log );
		echo '</pre>';
	}

}
$ip_logging = new CGC_IP_Login_Logging();
unset( $ip_logging );