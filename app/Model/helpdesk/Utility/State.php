<?php

namespace App\Model\helpdesk\Utility;

use App\BaseModel;

class State extends BaseModel
{
    protected $table = 'states';

    protected $fillable = ['code', 'name', 'created_by', 'updated_by'];

    public function districts()
    {
        return $this->hasMany(District::class, 'state_id');
    }
}
