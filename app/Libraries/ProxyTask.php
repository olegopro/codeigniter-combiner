<?php

namespace App\Libraries;

class ProxyTask
{
	public function setProxy($taskData)
	{
		$proxyData = [];
		if ($taskData['task_proxy_ip'] !== '') {
			$proxyData = [
				'proxyEnabled' => true,
				'proxy'        => [
					'mode' => $taskData['task_proxy_type'] == '' ? $taskData['task_proxy_type'] = 'http' : $taskData['task_proxy_type'],
					'host' => $taskData['task_proxy_ip'],
					'port' => $taskData['task_proxy_port'],
				]
			];

			if ($taskData['task_proxy_username'] && $taskData['task_proxy_password']) {
				$proxyData['proxy']['username'] = $taskData['task_proxy_username'];
				$proxyData['proxy']['password'] = $taskData['task_proxy_password'];

				return $proxyData;
			} else {
				$proxyData['proxy']['username'] = '';
				$proxyData['proxy']['password'] = '';

				return $proxyData;
			}
		} else {
			$proxyData['proxy']['mode'] = 'none';

			return $proxyData;
		}
	}
}
