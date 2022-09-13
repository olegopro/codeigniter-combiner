<?php

namespace App\Models;

use CodeIgniter\Model;

class TasksModel extends Model
{
	protected $table = 'tasks';
	protected $primaryKey = 'task_id';
	protected $allowedFields = [
		'task_firstname',
		'task_lastname',
		'task_day',
		'task_month',
		'task_year',
		'task_email',
		'task_telephone',
		'task_password',
		'task_status'
	];
}
