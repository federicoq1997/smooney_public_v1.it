<?php
class ioRequest{

	private static $userAgentName = "ioSmooney";
	private static $ContentType = 'application/json';
	private static $SecretKey = 'ec8f115a-052c-45e6-9254-d10f363adf82';
	private static $server;
	private static $headers;
	private static $timeout=10;


	public function __construct() { }

	private static function success($data = [],  $message = null) {
		return ["success" => true, "data" => $data,  "message" => $message];
	}

	private static function error($message, $data = null) {
		return ["success" => false, "error" => $message, "data" => $data];
	}

	public static function setSecretKey($key) {
		self::$SecretKey = $key;
	}
	public static function setServer($server) {
		self::$server = $server;
	}
	public static function setHeaders($headers) {
		self::$headers = $headers;
	}
	public static function setTimeout($timeout) {
		self::$timeout = $timeout;
	}

	/**
   * GET request
   * @param string $path
   * @param array $params
   */
	public static function get($endpoint, $args = []) {
		return self::request('GET',$endpoint,$args);
	}
	/**
   * POST request
   * @param string $path
   * @param array $params
   */
	public static function post($endpoint, $args = []) {
		return self::request('POST',$endpoint,$args);
	}
	/**
   * PUT request
   * @param string $path
   * @param array $params
   */
	public static function put($endpoint, $args = []) {
		return self::request('PUT',$endpoint,$args);
	}
	/**
   * DELETE request
   * @param string $path
   * @param array $params
   */
	public static function delete($endpoint, $args = []) {
		return self::request('DELETE',$endpoint,$args);
	}

	private static function request($request,$endpoint, $args = []){

		$curl = curl_init();
		$args = !empty($args)?$args:[];
		$url = self::$server.$endpoint;

		$headers = [];
		$headers[] = "Request-Smooney-App: ".self::$userAgentName;
		if(empty(self::$headers['Content-Type'])) self::$headers["Content-Type"] = self::$ContentType;
		if(!empty(self::$headers)) foreach(self::$headers as $name=>$value) $headers[] = $name.': '.$value;

		if($request=='GET'){
			$url.="?".(http_build_query($args));
			$args=[];
		}
		if(!empty($args)) $headers[] = "Signature-Smooney: ".hash('sha256', json_encode($args).'|'.self::$SecretKey);
		curl_setopt_array($curl,[
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST => strtoupper($request),
			CURLOPT_POSTFIELDS => ($request == 'POST' && self::$headers['Content-Type'] == 'application/json')? json_encode($args) : http_build_query($args),
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_SSL_VERIFYHOST=>0,
			CURLOPT_SSL_VERIFYPEER=>0,
			CURLOPT_SSL_VERIFYPEER=>0,
			CURLOPT_TIMEOUT=>self::$timeout,
		]);
		try {
			$curl_response = curl_exec($curl);
			if ($curl_response === false) throw new Exception(curl_error($curl));
			$http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ($http_status != 200) throw new Exception('Errore nella richiesta: status ' . $http_status);

			$data = json_decode($curl_response, true);
			if(!isset($data['success'])) $data = self::success($data);
			return $data;
			
		} catch (\Exception $e) {
			return self::error($e->getMessage());
		}	finally {
			curl_close($curl);
		}
	}
}

?>