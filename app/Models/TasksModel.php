<?php

namespace App\Models;

use CodeIgniter\Model;

class TasksModel extends Model
{
	protected $table = 'tasks';
	protected $primaryKey = 'task_id';
	protected $allowedFields = ['task_fio', 'task_telephone', 'task_summa', 'task_status'];
}
