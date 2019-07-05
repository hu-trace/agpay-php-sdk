<?php
	set_time_limit(0);
	
	include_once 'waya_pay_sdk.php';
	
	Config::build('Yd3rSG7ync', '1b7a48935d71b27dbc74a03e6b98d780');
	
	function alipay() {
		$module = HttpClient::module_order_alipay();
		$module->parameter(array(
			'fee'=>1,
			'body'=>'测试支付',
			'notifyUrl'=>'http://pay.wayakeji.com/api/testnotify'
		));
		$res = $module->execute();
		var_dump(json_encode($res));
	}
	
	function wxpay() {
		$module = HttpClient::module_order_wxpay();
		$module->parameter(array(
			'fee'=>1,
			'body'=>'测试支付',
			'notifyUrl'=>'http://pay.wayakeji.com/api/testnotify'
		));
		$res = $module->execute();
		var_dump(json_encode($res));
	}
	
	function refund($tradeNo) {
		$module = HttpClient::module_order_refund();
		$module->parameter(array(
			'tradeNo'=>$tradeNo,
			'refundFee'=>1,
			'totalFee'=>1
		));
		$res = $module->execute();
		var_dump(json_encode($res));
	}
	
	function queryOne($tradeNo) {
		$module = HttpClient::module_order_query_one();
		$module->parameter(array(
			'tradeNo'=>$tradeNo
		));
		$res = $module->execute();
		var_dump(json_encode($res));
	}
	
	function notifySign() {
		$text = '{"tradeNo":"1562236333104920204352796968","createTime":"2019-07-04 18:32:13","fee":1,"sign":"678E4C9789E198E1EB4A19C0DACF9569","paySource":"alipay","detail":"","body":"测试支付","status":2}';
		$array = json_decode($text, true);
		$oldSign = $array['sign'];
		foreach($array as $key => $value) {
			if($value === 'null' || $value === '') {
				unset($array[$key]);
			}
		}
		unset($array['sign']);
		ksort($array);
		$qs = http_build_query($array);
		echo $qs;
		echo "\r\n";
		$sign = strtoupper(md5('HVhCYq42DcUtcJDxU2dSvi4AHOF.'.$qs));
		echo $sign.'-----'.$oldSign;
		echo "\r\n";
		echo 'if it matches : '.($sign === $oldSign ? 'true' : 'false');
	}
	
//	wxpay();
//	alipay();
//	queryOne('1562236333104920204352796968');
//	refund('1562236333104920204352796968');
	notifySign();
?>