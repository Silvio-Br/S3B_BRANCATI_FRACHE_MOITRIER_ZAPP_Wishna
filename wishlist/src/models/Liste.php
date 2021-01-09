<?php

namespace wishlist\models;
use Illuminate\Database\Eloquent\Model;

class Liste extends Model
{

    protected $table = 'liste';
    protected $primaryKey = 'no';

    public function usesTimestamps() : bool
    {
        return false;
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'liste_id');
    }

}