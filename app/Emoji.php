<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emoji extends Model
{
    protected $fillable = ['name', 'value', 'src', 'alt'];
}
