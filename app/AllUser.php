<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AllUser extends Model
{
    protected $table = 'allusers';
    protected $primaryKey='openid';
}
