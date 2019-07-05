<?php
class Config {
	private static $domain = "http://pay.wayakeji.com";
	private static $api_id;
	private static $api_secret;
	private static $client_id;
	private static $token;
	private static $scpsat_secret_key;
	private static $session_char;
	
	public static function build($api_id, $api_secret) {
		Config::$api_id = $api_id;
		Config::$api_secret = $api_secret;
	}
	
	static function set_interface_info($client_id, $scpsat_secret_key, $session_char, $token) {
		Config::$client_id = $client_id;
		Config::$scpsat_secret_key = $scpsat_secret_key;
		Config::$session_char = $session_char;
		Config::$token = $token;
	}
	
	public static function api_id() {
		return Config::$api_id;
	}
	
	public static function api_secret() {
		return Config::$api_secret;
	}
	
	public static function client_id() {
		return Config::$client_id;
	}
	
	public static function token() {
		return Config::$token;
	}
	
	public static function scpsat_secret_key() {
		return Config::$scpsat_secret_key;
	}
	
	public static function session_char() {
		return Config::$session_char;
	}
	
	public static function domain() {
		return Config::$domain;
	}
	
}
class Http {

	private static $headers = array("Content-Type: application/json");

	public function get($module, $param, $headers) {
		if($param != null) {
			if(strstr($module, '?')) {
				$module = "{$module}&".http_build_query($param);
			}else {
				$module = "{$module}?".http_build_query($param);
			}
		}
		$url = Config::domain().$module;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers($headers));
		$result = curl_exec($ch);
		curl_close($ch);
		return json_decode($result, true);
	}

	public function post($module, $param, $headers) {
		$url = Config::domain().$module;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($param));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers($headers));
		$result = curl_exec($ch);
		curl_close($ch);
		return json_decode($result, true);
	}

	private function headers($headers) {
		if(is_null($headers)) {
			return Http::$headers;
		}
		return array_merge($headers, Http::$headers);
	}

	public function token() {
		$obj = $this->get('/api/token', array('apiId'=>Config::api_id(), 'apiSecret'=>Config::api_secret()), null);
		if($obj['code'] == 0) {
			$data = $obj['data'];
			Config::set_interface_info($data['clientId'], $data['scpsatSecretKey'], $data['sessionChar'], $data['token']);
		}
	}

}

interface HttpModule {

	function parameter($array);

	function execute();

}

abstract class AbstractHttpModule implements HttpModule {

	private $headers;
	private $parameter;
	private $sign_header;

	public abstract function module();
	public abstract function method();

	function init_headers() {
		$this->headers = array(
			'Scpsat-Token: '.Config::token(),
			'Scpsat-Char: '.Config::session_char(),
			$this->sign_header
		);
	}

	public function parameter($array) {
		$this->parameter = $array;
	}
	
	private function sign() {
		ksort($this->parameter);
		$qs = http_build_query($this->parameter);
		$this->sign_header = 'Scpsat-Sign: '.md5(Config::scpsat_secret_key().'.'.$qs);
	}

	public function execute() {
		$http = new Http();
		$method = strtoupper($this->method());
		if(Config::client_id() == null) {
			$http->token();
		}
		$module = $this->module().'?clientId='.Config::client_id();
		$res = $this->executor($http, $module, $method);
		if($res['code'] == -1 && $res['status'] == 6004) {
			$http->token();
			$res = $this->executor($http, $module, $method);
		}
		return $res;
	}
	
	private function executor($http, $module, $method) {
		$this->sign();
		$this->init_headers();
		if($method === 'GET') {
			return $http->get($module, $this->parameter, $this->headers);
		}else if($method === 'POST') {
			return $http->post($module, $this->parameter, $this->headers);
		}else {
			throw new Exception("There is no such request [ {$method} ]", -1);
		}
	}

}

class ApiOrderWxpay extends AbstractHttpModule {
	function module() {
		return '/api/wxorder';
	}
	function method() {
		return 'post';
	}
}

class ApiOrderAlipay extends AbstractHttpModule {
	function module() {
		return '/api/aliorder';
	}
	function method() {
		return 'post';
	}
}

class ApiOrderRefund extends AbstractHttpModule {
	function module() {
		return '/api/refund';
	}
	function method() {
		return 'post';
	}
}

class ApiOrderQueryOne extends AbstractHttpModule {
	function module() {
		return '/api/order';
	}
	function method() {
		return 'get';
	}
}

class HttpClient {
	
	public static function module_order_wxpay() {
		return new ApiOrderWxpay();
	}
	
	public static function module_order_alipay() {
		return new ApiOrderAlipay();
	}
	
	public static function module_order_refund() {
		return new ApiOrderRefund();
	}

	public static function module_order_query_one() {
		return new ApiOrderQueryOne();
	}
	
}
?>