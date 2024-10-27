<?php
/**
* Plugin Name: ADR Promotion Recommendations
* Plugin URI: https://www.adrelevantis.com/wp/plugin.html
* Version: 1.0.0
* Author: AdRelevantis
* Author URI: https://www.adrelevantis.com/
* Description: Display promotions based on page content. You still need to register your site at https://www.adrelevantis.com/wp/plugin-register.php to start serving you promotions.
* License: GPL2
*/

/*  Copyright 2018 AdRelevantis, LLC

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

register_activation_hook( __FILE__, array( 'AdrCdpRecommendations', 'plugin_activation' ) );
add_filter( 'the_content', array( 'AdrCdpRecommendations', 'cdp_attach' ) );

class AdrCdpRecommendations {
	public static function plugin_activation() {
		$pub = $_SERVER['SERVER_NAME'];
		update_option( 'adrcdp_publisher', $pub );
	}

	public static function cdp_attach( $content ) {
		$pub = get_option( 'adrcdp_publisher' );
		$auth = get_option( 'adrcdp_publisher_auth' );
		if (!$auth || $auth == 0) {
			$auth = adrcdp_check_publisher_auth($pub);
		}
		if ($auth && $auth == 1) {
			$content .= '<script async src="//www.adrelevantis.com/pub/wpcontent.js"><!-- --></script><div class="adr_ad2"><div class="pe-wrapper"><ul class="pe-module"><li class="pe-article"><ins data-revive-zoneid="8" data-revive-promotion="1" data-revive-publisher="' . $pub . '" data-revive-promoter="PRODUCT" data-revive-id="8d00ec3e74b449269c484e2fbed6f23a"></ins></li><li class="pe-article"><ins data-revive-zoneid="8" data-revive-promotion="1" data-revive-publisher="' . $pub . '" data-revive-promoter="PRODUCT" data-revive-id="8d00ec3e74b449269c484e2fbed6f23a"></ins></li></ul></div></div>';
		}
		
		return $content;
	}
}

function adrcdp_check_publisher_auth($pub) {
	$body = array(
		'publisher' => $pub
	);

	$args = array(
		'body' => $body
	);
	
	$result = wp_remote_post('https://www.compariola.com/ContentDrivenPromotion/WpRegisterAuth', $args);
	if (!empty($result)) {
		$result = json_decode($result['body'], true);
		$auth = $result['Auth'] == "YES" ? 1 : 0;
		update_option( 'adrcdp_publisher_auth', $auth );
		return $auth;
	}
	return false;
}

class AdrCdp_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'adrcdp_widget',
			__( 'AdrCdp Widget' , 'adrcdp'),
			array( 'description' => __( 'Display recommendations' , 'adrcdp') )
		);
	}
	
	function widget( $args, $instance ) {
		$pub = get_option( 'adrcdp_publisher' );
		$auth = get_option( 'adrcdp_publisher_auth' );
		if (!$auth || $auth == 0) {
			$auth = adrcdp_check_publisher_auth($pub);
		}
		if ($auth && $auth == 1) {
?>
	<script async src="//www.adrelevantis.com/pub/wpcontent.js"><!-- --></script>
	<div class="adr-ad"><a href="#" style="float:right;" onclick="event.preventDefault();this.parentNode.parentNode.removeChild(this.parentNode)"><img src="https://www.adrelevantis.com/img/cancel.png" alt="x"></a><ins data-revive-zoneid="8" data-revive-promotion="3" data-revive-publisher="<?php echo $pub; ?>" data-revive-promoter="PRODUCT" data-revive-id="8d00ec3e74b449269c484e2fbed6f23a"></ins>
	</div>
<?php
		}
		else {
?>
	<p>You are not authorized to display recommendations. Please register at https://www.adrelevantis.com/plugin-register.php.</p>
<?php
		}
	}
}

function adrcdp_register_widgets() {
	register_widget( 'AdrCdp_Widget' );
}
add_action( 'widgets_init', 'adrcdp_register_widgets' );
