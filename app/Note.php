<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'title'
    ];

    protected $hidden = [
        'pivot'
    ];

    public function attachUser(User $user, $rights = [])
    {
        $this->users()->attach($user->id, $rights);
    }

    public function users()
    {
        return $this->belongsToMany('App\User', 'note_user', 'note_id', 'user_id');
    }

    public function getUsers()
    {
        return $this->users()->get();
    }

    public function canUserRead($userId)
    {
        return $this->belongsToMany('App\User')->wherePivot('user_id', $userId)->exists();
    }

    public function canUserModify($userId)
    {
        return $this->belongsToMany('App\User')->wherePivot('user_id', $userId)->wherePivot('can_modify', 1)->exists();
    }

    public function canUserShare($userId)
    {
        return $this->belongsToMany('App\User')->wherePivot('user_id', $userId)->wherePivot('can_share', 1)->exists();
    }

    public function canUserDelete($userId)
    {
        return $this->belongsToMany('App\User')->wherePivot('user_id', $userId)->wherePivot('can_delete', 1)->exists();
    }
}
