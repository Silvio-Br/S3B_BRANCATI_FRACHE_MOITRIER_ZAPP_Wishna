<?php

namespace wishcreate\models;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{

    protected $table = 'item';
    protected $primaryKey = 'id';

    public function liste()
    {
        return $this->belongsTo(Liste::class, 'liste_id');
    }

    public function usesTimestamps() : bool
    {
        return false;
    }

}