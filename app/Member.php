<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $guarded = ['id'];

    public function identities()
    {
        return $this->hasMany(Identity::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)->withPivot(['reference']);
    }

}
