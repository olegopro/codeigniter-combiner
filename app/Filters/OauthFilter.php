<?php

namespace App\Filters;

use App\Libraries\Oauth;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use OAuth2\Request;
use OAuth2\Response;

class OauthFilter implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		$oauth = new Oauth;
		$request = Request::createFromGlobals();

		if (!$oauth->server->verifyResourceRequest($request)) {
			$oauth->server->getResponse()->send();
			die();
		}
	}

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// Do something here
	}
}
