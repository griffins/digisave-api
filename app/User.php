<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'country_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function roles()
    {
        return $this->hasMany(OrganizationRole::class);
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = phone($value, $this->country_code)->formatE164();
    }

    public function getPhoneAttribute($value)
    {
        $phone = phone($this->attributes['phone_number'], $this->country_code);
        return $phone->formatInternational();
    }

    public function getPhotoAttribute()
    {
        return asset('images/avatar.png');
    }

    public function claims()
    {
        return [];
    }

    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_roles');
    }
}
