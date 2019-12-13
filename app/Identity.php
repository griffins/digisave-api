<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
