<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $guarded = ['id'];

    public function apps()
    {
        return $this->hasMany(App::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
