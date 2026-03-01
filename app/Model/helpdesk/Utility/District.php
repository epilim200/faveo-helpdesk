<?php

namespace App\Model\helpdesk\Utility;

use App\BaseModel;

class District extends BaseModel
{
    protected $table = 'districts';

    protected $fillable = ['code', 'name', 'state_id', 'created_by', 'updated_by'];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
