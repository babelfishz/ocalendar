<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Input; 
use GuzzleHttp\Client; 
use App\User;
use Crypt;
use Log;


class wechatApiController extends Controller
{

	public function getcode(Request $request) 
	{ 
		//log::info($request);

		$code = $request->get('code'); 
		//$encryptedData = $request->get('encryptedData'); 
		//$iv = $request->get('iv'); 

		log::info($code);

		$appid = "wx743bb595feb128c4"; 
		$secret = "a6d5d99d069ffbd2326d5c112763929f"; 

		$client = new \GuzzleHttp\Client(); 
		$res = $client->request('GET', 'https://api.weixin.qq.com/sns/jscode2session', [
			'query' => [
				'appid' =>$appid, 
				'secret' => $secret, 
				'js_code' => $code, 
				'grant_type' => 'authorization_code'
			] 
		]); 

		$body = json_decode($res->getBody()); 
		$userId = $body->openid;
		$encryptUserId = Crypt::encrypt($userId);
		//$session_key = $body->session_key;

		return($encryptUserId);

		//return(Crypt::encrypt($openid)); 

		/*$userifo = new \WXBizDataCrypt($appid, $session_key); 

		$errCode = $userifo->decryptData($encryptedData, $iv, $data); 

		$info = json_decode($data); 
		$nickName = $info->nickName; 
		$avatarUrl = $info->avatarUrl; 
		$province = $info->province; 
		$city = $info->city;*/

		/*$user = User::where('open_id', '=', $openid)->first();
		if ($user == null){
			$user = new User; 
			$user->open_id = $openid;
			$user->save(); 
		}*/

		/*if (!$user->find($openid)) 
		{ 
			$user->openid = $openid; 
			//$user->session_key = $session_key; 
			//$user->nickName = $nickName; 
			//$user->avatarUrl = $avatarUrl; 
			//$user->province = $province; 
			//$user->city = $city; 
			//$user->save(); 
		};*/ 

		/*if ($errCode == 0) { 
				return ($data); 
		} else { 
				return ($errCode); 
		};*/

		/*$url = resolve('Illuminate\Http\Request')->getSchemeAndHttpHost().'/oauth/token';
		
		$http = new \GuzzleHttp\Client;
		$response = $http->post($url, [
    		'form_params' => [
        		'grant_type' => 'password',
        		'client_id' => '3',
        		'client_secret' => 'W6IlY7Zp1Fh995E5axaksO9yTVH5U4nHDG8uycD2',
        		'username' => $nickName,
        		'password' => 'password',
        		//'scope' => '',
    		],
		]);*/

		//return json_decode((string) $response->getBody(), true);
	}
}
