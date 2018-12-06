<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; 
use Illuminate\Support\Facades\Input; 
use GuzzleHttp\Client; 
use App\UserInfo;
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

		//log::info($code);

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

		return($encryptUserId);

		
	}

	public function indexUserInfo(Request $request){

		$userId = $request->Input('userId');
		$openid = Crypt::decrypt($userId);
		$allUserInfo = UserInfo::all();
		
		$stack = [];
		foreach ($allUserInfo as $userInfo) {
			$data = new \StdClass();
   			$data->userId = ($openid == $userInfo->openid) ? $userId : Crypt::encrypt($userInfo->openid);
   			$data->avatarUrl = $userInfo->avatarUrl;
   			$data->nickName = $userInfo->nickName;
   			$data->city = $userInfo->city;
   			$data->rovince =$userInfo->province;
   			array_push($stack, $data);
   		}

		return $stack;
	}

	public function updateUserInfo(Request $request){

		$userId = Crypt::decrypt($request->Input('userId'));

		$userInfo = UserInfo::where('openid', '=', $userId)->first();

		
		if ($userInfo == null){
			$userInfo = new UserInfo; 
			$userInfo->openid = $userId;
		}

		$userInfo->nickName = $request->Input('nickName');
		$userInfo->avatarUrl = $request->Input('avatarUrl');
		$userInfo->province =  $request->Input('province');
		$userInfo->city = $request->Input('city');

		$errCode = $userInfo->save(); 
	}

	public function deleteUserInfo($userId){

		$openid = Crypt::decrypt($userId);
		$destroy = UserInfo::destroy($openid);
	}
}
