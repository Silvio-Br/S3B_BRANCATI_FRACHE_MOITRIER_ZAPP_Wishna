<?php

namespace wishlist\models;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    protected $table = 'item';
    protected $primaryKey = 'id';

    public function usesTimestamps() : bool
    {
        return false;
    }

    public function liste()
    {
        return $this->belongsTo(Liste::class, 'liste_id');
    }
}