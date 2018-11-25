<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Crypt;
use Log;

class User extends Authenticatable
{
    //use Notifiable;
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /*public function findForPassport($username) {
        log::info('excuted');
        return $this->where('open_id', $username)->first();
    }

    public function validateForPassportPasswordGrant($password)
    {
        $decrypted = Crypt::decryptString($password);
        if ($decrypted == $this->open_id) {
            return true;
        }
        return false;
    }*/
}
