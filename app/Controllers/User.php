<?php

namespace App\Controllers;

use App\Libraries\Oauth;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;
use OAuth2\Request;
use stdClass;

class User extends BaseController
{
	use ResponseTrait;

	public function login(): object|null
	{
		$oauth = new Oauth;
		$request = Request::createFromGlobals();
		$respond = $oauth->server->handleTokenRequest($request);

		$code = $respond->getStatusCode();
		$body = $respond->getResponseBody();

		$rules = [
			'username' => 'required',
			'password' => 'required',
		];

		if (!$this->validate($rules)) {
			// get codeigniter errors
			return $this->fail($this->validator->getErrors());
		} elseif ($code === 401) {
			// get errors from oauth2
			foreach (json_decode($body) as $key => $value) {
				if ($key === 'error_description') {
					return $this->fail($value);
				}
			}
		} else {
			return $this->respond(json_decode($body), $code);
		}

		return null;
	}

	public function register()
	{
		$data = [];

		if (!$this->request->getPost()) {
			return $this->fail('Можно использовать только POST запросы');
		}

		$rules = [
			'username' => 'required|min_length[5]',
			'password' => 'required|min_length[5]',
			'password_confirm' => 'matches[password]',
			'first_name' => 'required|min_length[5]',
			'last_name' => 'required|min_length[5]',
			'email' => 'required|min_length[5]',
		];

		if (!$this->validate($rules)) {
			return $this->fail(implode('<br>', $this->validator->getErrors()));
		} else {
			$model = new UserModel;

			$data = [
				'username' => $this->request->getVar('username'),
				'password' => $this->request->getVar('password'),
				'first_name' => $this->request->getVar('first_name'),
				'last_name' => $this->request->getVar('last_name'),
				'email' => $this->request->getVar('email')
			];

			$user_id = $model->insert($data);
			$data['id'] = $user_id;

			unset($data['password']);

			return $this->respondCreated($data);
		}
	}
}
