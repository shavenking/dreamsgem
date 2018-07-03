<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property User user
 */
class Wallet extends Model implements Operatable
{
    use OperatableTrait;

    const GEM_QI_CAI = 0; // 七彩
    const GEM_DUO_XI = 1; // 多喜
    const GEM_DUO_FU = 2; // 多福
    const GEM_DUO_CAI = 3; // 多財
    const GEM_DREAMSGEM = 4; // 夢寶積分
    const GEM_C = 5; // 碳幣
    const GEM_GOLD_GOD = 6; // 財神幣
    const GEM_USD = 7; // 美金
    const GEM_DREAMS = 8; // 圓夢積分

    const REWARD_ACTIVATE_DRAGON = '100.0';
    const REWARD_ACTIVATE_TREE = '5.0';
    const BUY_TREE_PROGRESS_REWARD_SMALL = '300.0';
    const BUY_TREE_PROGRESS_REWARD_MEDIUM = '700.0';
    const BUY_TREE_PROGRESS_REWARD_LARGE = '1200.0';

    protected $fillable = ['user_id', 'gem', 'amount', 'external_address'];

    public function gems()
    {
        return [
            self::GEM_QI_CAI,
            self::GEM_DUO_XI,
            self::GEM_DUO_FU,
            self::GEM_DUO_CAI,
            self::GEM_DREAMSGEM,
            self::GEM_C,
            self::GEM_GOLD_GOD,
            self::GEM_USD,
            self::GEM_DREAMS,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function walletTransferApplications()
    {
        return $this->hasMany(WalletTransferApplication::class, 'from_wallet_id');
    }

    public function getExternalWalletAttribute()
    {
        return in_array($this->gem, [
            self::GEM_C,
            self::GEM_GOLD_GOD,
            self::GEM_USD,
            self::GEM_DREAMS
        ], true);
    }

    /**
     * @param $type
     * @return mixed
     * @see Tree
     */
    public function treePrice($type)
    {
        return data_get([
            '1000.0', // TYPE_SMALL
            '2000.0', // TYPE_MEDIUM
            '3000.0', // TYPE_LARGE
        ], $type, '1000.0');
    }

    public function allowedToApplyTransfer()
    {
        return in_array($this->gem, [
            self::GEM_QI_CAI, // 七彩
//            self::GEM_DUO_XI, // 多喜
//            self::GEM_DUO_FU, // 多福
            self::GEM_DUO_CAI, // 多財
        ], true);
    }

    public function walletTransferMap()
    {
        return collect([
            // 七彩 => 碳幣、財神幣、美金、圓夢積分
            self::GEM_QI_CAI => [self::GEM_C, self::GEM_GOLD_GOD, self::GEM_DREAMS],
            // 多喜
            self::GEM_DUO_XI => [],
            // 多福
            self::GEM_DUO_FU => [],
            // 多財 => 碳幣
            self::GEM_DUO_CAI => [self::GEM_C, self::GEM_GOLD_GOD, self::GEM_DREAMS],
        ]);
    }

    public function transferRateTextMap()
    {
        $map = collect($this->gems())->crossJoin($this->gems())->map(function ($pair) {
            return implode(':', $pair);
        });

        $rate = collect(array_fill(0, $map->count(), '1:1'));

        return $map->combine($rate)->mapWithKeys(function ($rate, $pair) {
            if ((int) explode(':', $pair)[0] === self::GEM_DUO_CAI) {
                return [$pair => '7:7'];
            }

            return [$pair => $rate];
        });
    }

    public function allowedToGetTransferFrom(Wallet $wallet)
    {
        return in_array(
            $this->gem,
            data_get($this->walletTransferMap(), $wallet->gem, []),
            true
        );
    }

    public function buyTreeReward($treeType)
    {
        return [
            Tree::TYPE_SMALL => Wallet::BUY_TREE_PROGRESS_REWARD_SMALL,
            Tree::TYPE_MEDIUM => Wallet::BUY_TREE_PROGRESS_REWARD_MEDIUM,
            Tree::TYPE_LARGE => Wallet::BUY_TREE_PROGRESS_REWARD_LARGE,
        ][$treeType];
    }
}
