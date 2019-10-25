<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    protected $keyType = 'string';
    protected $primaryKey = 'openid';
}
