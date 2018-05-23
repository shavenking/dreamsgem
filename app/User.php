<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Kalnoy\Nestedset\NodeTrait;
use Laravel\Passport\HasApiTokens;

/**
 * @property int id
 */
class User extends Authenticatable implements Operatable
{
    use Notifiable, NodeTrait, HasApiTokens, OperatableTrait;

    const MAX_ACTIVATE_TREE_AMOUNT = 50;
    const DEFAULT_TREE_CAPACITY = 90;
    const MAX_CHILDREN_FOR_ONE_USER = 7;

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
        'user_id',
        '_lft',
        '_rgt',
       'parent_id',
    ];

    protected $casts = [
        'frozen' => 'boolean',
    ];

    protected $appends = [
        'is_child_account',
        'activated',
    ];

    public function parentAccount()
    {
        return $this->hasOne(User::class);
    }

    public function childAccounts()
    {
        return $this->hasMany(User::class);
    }

    public function dragons()
    {
        return $this->hasMany(Dragon::class, 'owner_id');
    }

    public function activatedDragon()
    {
        return $this->hasOne(Dragon::class);
    }

    public function trees()
    {
        return $this->hasMany(Tree::class, 'owner_id');
    }

    public function activatedTrees()
    {
        return $this->hasMany(Tree::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function treeSettlementHistories()
    {
        return $this->hasMany(TreeSettlementHistory::class);
    }

    public function activateTree(Tree $tree, User $targetUser)
    {
        $treeTable = $tree->getTable();
        $maxActivateTreeAmount = self::MAX_ACTIVATE_TREE_AMOUNT;

        $affectedCount = Tree::whereId($tree->id)
            ->where('user_id', null)
            ->whereRaw(
                "$maxActivateTreeAmount > (
                    SELECT count(*)
                    FROM (SELECT * FROM $treeTable WHERE user_id = {$targetUser->id}) AS tree_temp 
                )"
            )
            ->update(
                [
                    'user_id' => $targetUser->id,
                ]
            );

        return $affectedCount;
    }

    public function getIsChildAccountAttribute()
    {
        return $this->user_id !== null;
    }

    public function addDownline(User $user)
    {
        $possibleParents = collect([$this]);

        while ($possibleParent = $possibleParents->shift()) {
            if (!$possibleParent->activated) {
                continue;
            }

            $children = $possibleParent->children()->get();

            if ($children->count() === User::MAX_CHILDREN_FOR_ONE_USER) {
                $possibleParents = $possibleParents->merge($children);
            } else {
                break;
            }
        }

        // validate if specific upline is activated
        static::whereId($possibleParent->id)
            ->whereFrozen(false)
            ->whereHas('activatedDragon')->firstOrFail();

        $possibleParent->appendNode($user);
    }

    public function getActivatedAttribute()
    {
        return !is_null($this->activatedDragon);
    }

    public function toArray()
    {
        return $this->attributesToArray();
    }
}
