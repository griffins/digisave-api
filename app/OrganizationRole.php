<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrganizationRole extends Model
{
    protected $guarded = ['id'];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
