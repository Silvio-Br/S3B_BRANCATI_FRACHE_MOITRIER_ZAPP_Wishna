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

    /**
     * methode retournant l'item correspondant à l'id donné en param
     * @param $id
     *              id de l'item recherché
     * @return Item
     */
    public static function item($id) {
        return Item::where('id','=',$id);
    }

    /**
     * methode retourne les listes contenant cet item
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function liste()
    {
        return $this->belongsTo(Liste::class, 'liste_id');
    }
}