<?php

namespace wishlist\models;
use Illuminate\Database\Eloquent\Model;


class Compte extends Model
{
    protected $table = 'compte';
    protected $primaryKey = 'iduser';

    public function usesTimestamps() : bool
    {
        return false;
    }

    public static function login($user, $mdp) {
        $resultat = (Compte::where([['userName', '=', $user], ['password', '=', $mdp]])->get())->first();
        if (isset ($resultat->iduser)) {
            $_SESSION['isConnect'] = true;
            $_SESSION['userName'] = $resultat->userName;
        }
    }

}