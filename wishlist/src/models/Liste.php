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
     * methode retournant les listes étant publiques
     * @return mixed
     */
    public static function listePublique()
    {
        return Liste::where([['etrePublique', '=', '1'], ['expiration', '>=', new \DateTime("now")]]);
    }

    /**
     * methode retournant les listes étant publiques depuis la date donnée
     * @return mixed
     */
    public static function listePubliqueDepuisDate(String $date)
    {
        return Liste::where([['etrePublique', '=', '1'], ['expiration', '>=', new \DateTime($date)]]);
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