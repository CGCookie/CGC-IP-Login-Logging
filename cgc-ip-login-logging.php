<?php
/*
 * Plugin Name: CGC IP Login Logging
 */


class CGC_IP_Login_Logging {

	function __construct() {

		add_action( 'set_logged_in_cookie', array( $this, 'log_ip' ), 10, 5 );
		add_action( 'wp_footer', array( $this, 'test' ) );

	}

	function log_ip( $logged_in_cookie, $expire, $expiration, $user_id, $status = 'logged_in' ) {

		if( 'logged_in' == $status ) {

			$log = (array)get_user_meta( $user_id, '_cgc_login_ips', true );
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
				'ip'   => $ip,
				'geo'  => $response,
				'time' => current_time( 'timestamp' )
			);

		}

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