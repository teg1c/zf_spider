<?php


namespace App\HttpController;

use EasySwoole\Http\Message\Status;
use EasySwoole\HttpClient\HttpClient;

class Index extends Base
{
	protected $url = "http://218.61.108.169";
	
	function index()
	{
		$request  = $this->request();
		$username = $request->getRequestParam('username');
		$password = $request->getRequestParam('password');
		if (!$username || !$password) {
			return $this->writeJson(Status::CODE_BAD_REQUEST, null, '信息填写完整');
		}
		include_once EASYSWOOLE_ROOT . '/Extend/rsa/Crypt/RSA.php';
		$response = $this->curl('GET', $this->url . "/jwglxt/xtgl/login_slogin.html?language=zh_CN&_t=" . time());//执行请求
		$header   = $response->getClient();
		$cookies  = $header->cookies;
		
		
		$response = $this->curl('GET', $this->url . "/jwglxt/xtgl/login_getPublicKey.html?time=" . time() . "&_=" . time(), [], $cookies);
		$key      = json_decode($response->getBody(), true);
		$exponent   = $key['exponent'];
		$modulus    = $key['modulus'];
		$rsa        = new \Crypt_RSA();
		$mykey['n'] = new \Math_BigInteger(bin2hex(base64_decode($modulus)), 16);
		$mykey['e'] = new \Math_BigInteger(bin2hex(base64_decode($exponent)), 16);
		$rsa->loadKey($mykey, CRYPT_RSA_PUBLIC_FORMAT_RAW);
		$rsa->getPublicKey(CRYPT_RSA_ENCRYPTION_PKCS1);
		
		$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
		$res      = $rsa->encrypt($password);
		$password = base64_encode($res);
		
		$response = $this->curl('POST', $this->url . "/jwglxt/xtgl/login_slogin.html", [
			'form_params' => [
				'yhm' => $username,
				'mm'  => $password,
			]
		], $cookies);
		
		if (strpos($response->getBody(), '用户名或密码不正确') !== false) {
			return $this->writeJson(Status::CODE_BAD_REQUEST, null, '密码错误');
		}
		
		return $this->writeJson(Status::CODE_OK, null, 'success');
	}
	
	function curl(string $method, string $url, array $params = null, array $cookie = null)
	{
		$request = new HttpClient($url);
		$request->setClientSettings([ 'timeout' => 3, 'connect_timeout' => 3 ]);//配置客户端的配置(将覆盖原有配置),可参考https://wiki.swoole.com/wiki/page/726.html
		$defaultHeader = [
			"User-Agent" => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0'
		];
		switch ($method) {
			case 'GET' :
				if ($params && isset($params['query'])) {
					foreach ($params['query'] as $key => $value) {
					
					}
				}
				break;
			case 'POST' :
				if ($params && isset($params['form_params'])) {
					$request->post($params['form_params'], 'text/html;charset=UTF-8');
				}
				break;
			default:
				throw new \Exception("method error");
				break;
		}
		if ($cookie) {
			$request->addCookie('JSESSIONID', $cookie['JSESSIONID']);
		}
		if (isset($params['header']) && !empty($params['header']) && is_array($params['header'])) {
			$request->setHeaders(array_merge($params['header'], $defaultHeader));
			
		}
		
		return $request->exec();
	}
}