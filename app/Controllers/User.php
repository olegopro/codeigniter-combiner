<?php

namespace App\Controllers;

use App\Libraries\Oauth;
use CodeIgniter\API\ResponseTrait;
use OAuth2\Request;

class User extends BaseController
{
	use ResponseTrait;

	public function login()
	{
		$oauth = new Oauth;
		$request = new Request;
		$respond = $oauth->server->handleTokenRequest($request->createFromGlobals());

		$code = $respond->getStatusCode();
		$body = $respond->getResponseBody();

		return $this->respond(json_decode($body), $code);
	}
}
