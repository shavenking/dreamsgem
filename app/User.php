<?php

namespace App;

use App\Events\DragonActivated;
use App\Events\TreeCreated;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Kalnoy\Nestedset\NodeTrait;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable implements Operatable
{
    use Notifiable, NodeTrait, HasApiTokens, OperatableTrait;

    const MAX_ACTIVATE_DRAGON_AMOUNT = 1;
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

    public function activateDragon(Dragon $dragon, User $targetUser)
    {
        $maxActivateDragonAmount = self::MAX_ACTIVATE_DRAGON_AMOUNT;

        $affectedCount = Dragon::whereId($dragon->id)
            ->where('user_id', null)
            ->whereRaw(
                "$maxActivateDragonAmount > (
                    SELECT count(*) FROM 
                        (SELECT * FROM dragons WHERE user_id = {$targetUser->id}) AS dragon_temp 
                    WHERE user_id = {$targetUser->id}
                )"
            )
            ->update(
                [
                    'user_id' => $targetUser->id,
                ]
            );

        return $affectedCount;
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
            $children = $possibleParent->children()->get();

            if ($children->count() === User::MAX_CHILDREN_FOR_ONE_USER) {
                $possibleParents = $possibleParents->merge($children);
            } else {
                break;
            }
        }

        $possibleParent->appendNode($user);
    }
}
