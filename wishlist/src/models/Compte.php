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
        if (isset ($resultat->idCompte)) {
            $_SESSION['isConnect'] = true;
            $_SESSION['userName'] = $resultat->userName;
        }
    }

    public static function logout() {
        if ($_SESSION['isConnect']) {
            $_SESSION['isConnect'] = false;
            unset($_SESSION['userName']);
        }
    }

    public static function signUp($user, $mdp) {
        $existeDeja = Compte::where([['userName', '=', $user]])->get();
        if ($existeDeja->first() != null){
            return "Username existe dejà";
        } else {
            $tmp = new Compte();
            $tmp->userName = $user;
            $tmp->password = $mdp;
            $tmp->save();
            return "ok";
        }
    }
}