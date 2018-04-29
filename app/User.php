<?php

namespace App;

use App\Events\TreeCreated;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\NodeTrait;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements Operatable
{
    use Notifiable, NodeTrait, HasApiTokens, OperatableTrait;

    const MAX_TREE_AMOUNT = 3;
    const DEFAULT_TREE_CAPACITY = 90;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'frozen',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function parentAccount()
    {
        return $this->hasOne(User::class);
    }

    public function childAccounts()
    {
        return $this->hasMany(User::class);
    }

    public function dragon()
    {
        return $this->hasOne(Dragon::class);
    }

    public function trees()
    {
        return $this->hasMany(Tree::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function addTree()
    {
        $treeTable = (new Tree)->getTable();
        $maxTreeAmount = self::MAX_TREE_AMOUNT;
        $defaultTreeCapacity = self::DEFAULT_TREE_CAPACITY;

        DB::beginTransaction();

        /**
         * INSERT INTO trees (user_id, remain, capacity, progress)
         * SELECT $user->id, 0, $defaultTreeCapacity, 0.0
         * WHERE (SELECT COUNT(*) FROM trees WHERE user_id = $user->id) < User::MAX_TREE_CAPACITY;
         */
        $success = DB::insert(
            DB::raw(
                implode(' ', [
                    "INSERT INTO $treeTable (user_id, remain, capacity, progress)",
                    "SELECT {$this->id}, 0, $defaultTreeCapacity, 0.0",
                    "WHERE (SELECT COUNT(*) FROM $treeTable WHERE user_id = {$this->id}) < $maxTreeAmount"
                ])
            )
        );

        if ($success) {
            event(new TreeCreated(Tree::latest()->firstOrFail(), $this));
        }

        DB::commit();

        return $success;
    }
}
