<?php

/**
 * PHP class to connect WordPress with Google Analytics via measurement protocol
 * Reference Google url: https://developers.google.com/analytics/devguides/collection/protocol/v1/?hl=es
 * Send Google and Bing bots behavior monitoring to GA for further processing.
 *
 * @since      1.0.0
 * @package    seobot_Monitor
 * @subpackage seobot_Monitor/includes
 * @author     Santiago Alonso @salonsoweb <salonsoweb@gmail.com>
 */
class Seobot_Monitor {

	/**
	 * Returns the required variables for tracking
	 * Retrieves the values of the WordPress wp_options table.
	 *
	 * @since    1.0.0
	 * @var      string 	$bots_regex	Regular expression to delimit valid user-agents (for example, "/googlebot|bingbot/i")
	 * @var      string 	$ua			Property tracking code in Google Analytics (UA-XXXXXXX)
	 * @var      string 	$page_title	Page title we want to send to GA
	 * @var      string 	$http_code	Response code we want to send to GA. Usually 404 or 200
	 * @return   array 		Array with tracking configuration parameters
	 */
	static function getParams(){
		$ret = array();

		$ret['ua'] 			= get_option( 'seobot_monitor_ua', 'UA-XXXXXXXX' );
		$ret['page_title'] 	= self::get_current_title();
		$ret['http_code'] 	= (is_404()) ? '404' : '200';
		$ret['bots_regex'] 	= get_option( 'seobot_monitor_bots_regex', "/googlebot|bingbot/i" );

		return $ret;

	}

	/**
	 * Returns the title of the current page
	 * According to the plugin configuration, it will return the WordPress title or the one configured in YOAST SEO.
	 * If the current page is a 404 error it will return a generic title
	 *
	 * @since    1.0.0
	 * @return 	string 	Current Page Title
	 */
	static private function get_current_title(){

		if(is_404()){
			return get_option( 'seobot_monitor_404title', '404: página no encontrada' );
		}

		$title_origin = get_option( 'seobot_monitor_titleorigin', 'yoast' );

		switch ($title_origin) {
			case 'yoast':
				return get_post_meta( get_the_ID(), '_yoast_wpseo_title', true );		
				break;
			case 'wp':
			default:
				return get_the_title();
				break;
		}
	}


	/**
	 * If the User Agent of the visit matches the defined regular expression it constructs a valid request.
	 * Send the pageview to Google Analytics via measurement protocol.
	 *
	 * @since    1.0.0
	 * @param      string 	$type	Determines data structure for GA. Values: pageview, render_event (Default: pageview)
	 */
	static function track($type = 'pageview', $rendered_url = '', $userAgent = ''){

		$s = $_SERVER;
		$params = self::getParams();

		if(preg_match($params['bots_regex'], $s['HTTP_USER_AGENT'], $matches)){
			$bot = $matches[0];
			$botNicename = self::getBotName($bot, $userAgent);

			//Check real or fakebot
			$esGoogle = self::checkGooglebot($s['REMOTE_ADDR']);

			$data = array( 
				'v'	=> 1, 
				'tid'	=> $params['ua'],
				'cid'	=> self::generate_uuid(), 
				'dh'	=> $s['HTTP_HOST'], 
				'dl'	=> $s['REQUEST_URI'], 
				'dr'	=> isset($s['HTTP_REFERER'])? $s['HTTP_REFERER'] : '',
				'dp'	=> $s['REQUEST_URI'], 
				'dt'	=> $params['page_title'], 
				'ck'	=> $s['HTTP_USER_AGENT'], 
				'uip'	=> $s['REMOTE_ADDR'],
			);

			//Pageview custom params
			if($type == 'pageview'){
				$data['t']	= 'pageview';
				$data['cs'] = $bot;
				$data['cm'] = 'direct';
				$data['cn'] = ''; 
				$data['cc'] = '';
				$data['cd1'] = $s['HTTP_USER_AGENT'];
				$data['cd2'] = $params['http_code'];
			}

			//Event custom params
			if($type == 'render_event'){
				$data['t']	= 'event';
				$data['ni']	= 1;
				$data['ec']	= 'BotRender';
				$data['el']	= $userAgent." - ".$esGoogle. " ".$botname;
				$data['ea']	= $rendered_url;
			}

			$url = 'http://www.google-analytics.com/collect';
			$content = http_build_query($data);

			//self::debug_trace($data);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_USERAGENT, $s['HTTP_USER_AGENT']);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT_MS, 0);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch,CURLOPT_ENCODING , "gzip");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
			$result = curl_exec($ch);
			$info= curl_getinfo($ch);
			curl_close($ch);

		}
	}


	/**
	 * Generates the cid parameter for the Google Analytics measurement protocol.
	 * Used to anonymously identify a specific user, device or browser instance.
	 * This value must be a random UUID (version 4) as described at http://www.ietf.org/rfc/rfc4122.txt.
	 *
	 * @since     1.0.0
	 * @return    string    uuid random code generated
	 * @access   private
	 */
	static private function generate_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	/**
	 * Debug function
	 * Show var received on screen and die execution process
	 *
	 * @since     1.0.0
	 */
	static public function debug_trace($var,$die = false) {
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
		if($die) die();
	}

	/**
	 * Debug function
	 * Check if current IP is a real google hostname
	 *
	 * @since     2.0.0
	 */
	static private function checkGooglebot($ip){
		$hostname = gethostbyaddr($ip);    
		$ip_by_hostname=gethostbyname($hostname);   
		return (preg_match("/googlebot/i",$hostname) && ($ip_by_hostname == $ip)) ? 'RealGooglebot' : 'NotGooglebot';
	}


	/**
	 * Return bot nicename
	 * No solo queremos saber que es Google sino qué tipo de bot es, Mobile, Imagenes, Ads...
	 *
	 * @since     2.0.0
	 */
	static private function getBotName($ip, $userAgent){
		$botname = "--";
		$bots = [
				'Mediapartners-Google[ /]([0-9.]{1,10})' => 'Google Mediapartners',
				'Mediapartners-Google' => 'Google Mediapartners',
				'Googl(e|ebot)(-Image)/([0-9.]{1,10})' => 'Google Image',
				'Googl(e|ebot)(-Image)/' => 'Google Image',
				'^gsa-crawler' => 'Google',
				'Googl(e|ebot)(-Sitemaps)/([0-9.]{1,10})?' => 'Google-Sitemaps',
				'GSiteCrawler[ /v]*([0-9.a-z]{1,10})?' => 'Google-Sitemaps',
				'Googl(e|ebot)(-Sitemaps)' => 'Google-Sitemaps',
				'Mobile.*Googlebot' => 'Google-Mobile',
				'^AdsBot-Google' => 'Google-AdsBot',
				'^Feedfetcher-Google' => 'Google-Feedfetcher',
				'compatible; Google Desktop' => 'Google Desktop',
				'Googlebot' => 'Googlebot'
		];
		
		foreach( $bots as $pattern => $bot ) {
			if ( preg_match( '#'.$pattern.'#i' , $userAgent) == 1 ){
				$botname = preg_replace ( "/\\s{1,}/i" , '-' , $bot );
				break;
			}
		}

		return $botname;
	}


	/**
	 * Enqueue custom scripts
	 * Only if HTTP_USER_AGENT matches with regex in plugin config
	 *
	 * @since     2.0.0
	 */
	static public function enqueue_head_scripts() {

		$s = $_SERVER;
		$params = self::getParams();

		if(preg_match($params['bots_regex'], $s['HTTP_USER_AGENT'], $matches)){
			//Script JS
			wp_enqueue_script( 'seobotmonitor', plugins_url( '../js/seobotmonitor.js', __FILE__ ), ['jquery'], SEOBOT_MONITOR_VERSION, true);
			wp_localize_script( 'seobotmonitor', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		}
	}

	/**
	 * AJAX action for js Googlebot.
	 * Send the render event to Google Analytics via measurement protocol.
	 *
	 * @since     2.0.0
	 */
	static public function track_render_event() {
		$rendered_url = sanitize_text_field($_POST['url']);
		$userAgent =  sanitize_text_field($_POST['user_a']);;
		
		self::track('render_event', $rendered_url, $userAgent);

		wp_die();
	}
	
}
