<?php

namespace wishlist\models;
use Illuminate\Database\Eloquent\Model;


class Compte extends Model
{
    protected $table = 'compte';
    protected $primaryKey = 'idCompte';

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

    public static function changeMdp($user, $newMdp) {
        try{
            $compte = Compte::query()->where('userName', '=', $user)->firstOrFail();
            $compte->password = $newMdp;
            $compte->save();
            return "ok";
        } catch(\Exception $e) {
            echo $e->getMessage();
            return $e->getMessage();
        }
    }
}