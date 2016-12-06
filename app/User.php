<?php

namespace App;

use Illuminate\Notifications\Notifiable;
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
        'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'pivot'
    ];
    // more info: https://github.com/laravel/framework/issues/745

    public function notes()
    {
        //return $this->belongsToMany('App\Note', 'note_user', 'user_id', 'note_id');
        return $this->belongsToMany('App\Note');
    }

    public function getNotes() {
        return $this->notes()->get(['id', 'title', 'created_at', 'updated_at']);
    }

    public static function isRegistered($email)
    {
        $user = User::where('email', $email);
        return !empty($user);
    }
}
