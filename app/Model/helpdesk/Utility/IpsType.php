<?php

namespace App\Model\helpdesk\Utility;

use App\BaseModel;

class IpsType extends BaseModel
{
    protected $table = 'ips_types';

    protected $fillable = ['name', 'sort', 'created_by', 'updated_by'];
}
