<?php

namespace App\Models;

use CodeIgniter\Model;

class WebWalkerModel extends Model
{
    protected $table = 'web_walker_tasks';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'id',
        'entry_point',
        'internal_transitions',
        'proxy_type',
        'proxy_username',
        'proxy_password',
        'proxy_ip',
        'proxy_port',
        'status'
    ];
}
