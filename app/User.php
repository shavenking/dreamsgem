<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\NodeTrait;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use Notifiable, NodeTrait, HasApiTokens;

    const MAX_TREE_CAPACITY = 3;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'frozen',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function dragon()
    {
        return $this->hasOne(Dragon::class);
    }

    public function trees()
    {
        return $this->hasMany(Tree::class);
    }

    public function addTree()
    {
        $treeTable = (new Tree)->getTable();

        /**
         * INSERT INTO trees (user_id)
         * SELECT $user->id
         * WHERE (SELECT COUNT(*) FROM trees WHERE user_id = $user->id) < User::MAX_TREE_CAPACITY;
         */
        $maxTreeCapacity = self::MAX_TREE_CAPACITY;

        return DB::insert(
            DB::raw(
                implode(' ', [
                    "INSERT INTO $treeTable (user_id)",
                    "SELECT {$this->id}",
                    "WHERE (SELECT COUNT(*) FROM $treeTable WHERE user_id = {$this->id}) < $maxTreeCapacity"
                ])
            )
        );
    }
}
