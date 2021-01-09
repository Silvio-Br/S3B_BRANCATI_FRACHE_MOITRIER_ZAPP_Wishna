<?php

namespace wishcreate\models;
use Illuminate\Database\Eloquent\Model;

class Liste extends Model
{

    protected $table = 'liste';
    protected $primaryKey = 'no';

    public function items()
    {
        return $this->hasMany(Item::class, 'liste_id');
    }

    public function usesTimestamps() : bool
    {
        return false;
    }

}