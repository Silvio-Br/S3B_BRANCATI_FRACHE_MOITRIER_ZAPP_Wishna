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

    /**
     * methode retournant la liste ayant le token donné en param
     * @param $token
     * @return Liste
     */
    public static function liste($token) {
        return Liste::where('token','=',$token);
    }

    /**
     * methode retournant les items appartenant à cette liste
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'liste_id');
    }

}