<?php

namespace App;

use App\Http\Middleware\ReplaceHashids;
use Faker\Generator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vinkla\Hashids\Facades\Hashids;

class HashidsTransformer
{
    const NUMBER_OF_RANDOM_DIGIT_PREFIX = 2;
    const NUMBER_OF_RANDOM_DIGIT_POSTFIX = 2;

    public $faker;
    private $cache;

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
        $this->cache = [];
    }

    public function transform($target)
    {
        if ($target instanceof Model) {
            $this->transformModel($target);
            return $target;
        }

        if ($target instanceof Collection) {
            $this->transformCollection($target);
            return $target;
        }

        if ($target instanceof Paginator) {
            $this->transformPaginator($target);
            return $target;
        }

        return $target;
    }

    public function transformModel(Model &$target)
    {
        if ($target instanceof User) {
            $target->setIncrementing(false);
            $target->setAttribute(
                'id',
                $this->protect($target->getKey())
            );
        }

        if ($target instanceof OperationHistory) {
            $this->transformOperationHistoryResultData($target);
        }

        foreach (ReplaceHashids::$shouldReplace as $shouldReplaced) {
            if ($target->{$shouldReplaced} && is_int($target->{$shouldReplaced})) {
                $target->setAttribute($shouldReplaced, $this->protect($target->{$shouldReplaced}));
            }
        }

        foreach ($target->getRelations() as $relationName => $relation) {
            if ($relation) {
                $this->transform($relation);
                $target->setRelation($relationName, $relation);
            }
        }
    }

    public function transformCollection(Collection &$target)
    {
        $target->each(function (&$item) {
            return $this->transform($item);
        });
    }

    public function transformPaginator(Paginator &$target)
    {
        $target->each(function (&$item) {
            return $this->transform($item);
        });
    }

    public function transformOperationHistoryResultData(OperationHistory &$operationHistory)
    {
        $shouldReplace = ReplaceHashids::$shouldReplace;

        if ($operationHistory->operatable_type == $operationHistory->transformOperatableType(User::class)) {
            $shouldReplace += ['id'];
        }

        $resultData = $operationHistory->result_data;

        foreach ($shouldReplace as $shouldReplaced) {
            if (isset($resultData->$shouldReplaced)) {
                $resultData->$shouldReplaced = $this->protect($resultData->$shouldReplaced);
            }
        }

        $operationHistory->result_data = $resultData;
    }

    public function protect($original)
    {
        if (!((int) $original)) {
            return $original;
        }
        
        $this->faker->seed($original);

        $strPrefix = implode('', [
            $this->faker->randomElement(['A', 'B', 'C']),
            [
                ['A', 'S'], // 1
                ['F', 'P'], // 2
                ['H', 'E'], // 3
                ['X', 'O'], // 4
                ['Y', 'U'], // 5
                ['N', 'M'], // 6
                ['D', 'G'], // 7
                ['D', 'G'], // 8
                ['L', 'V'], // 9
                ['C', 'K'], // 10
                ['J', 'W'], // 11
                ['B', 'R'], // 12
            ][$this->findUser($original)->created_at->month - 1][$this->findUser($original)->created_at->second % 2],
            $this->faker->randomElement(['A', 'B', 'C']),
        ]);

        return implode('', [
            strtolower($strPrefix),
            $this->faker->randomNumber(self::NUMBER_OF_RANDOM_DIGIT_PREFIX, true),
            $original,
            $this->faker->randomNumber(self::NUMBER_OF_RANDOM_DIGIT_PREFIX, true),
        ]);
    }

    public function decode($protected)
    {
        if ($decoded = array_first(Hashids::decode($protected))) {
            return $decoded;
        }

        if (
            preg_match('/[a-c]{1}[a-z]{1}[a-c]{1}[0-9]{' . self::NUMBER_OF_RANDOM_DIGIT_PREFIX . '}([0-9]+)[0-9]{' . self::NUMBER_OF_RANDOM_DIGIT_POSTFIX . '}/', $protected, $matches)
            && count($matches) === 2
            && $this->protect($matches[1]) === $matches[0]
        ) {
            return $matches[1];
        }

        return '';
    }

    private function findUser($id)
    {
        if (!isset($this->cache[$id])) {
            $this->cache[$id] = User::find($id);
        }

        return $this->cache[$id];
    }
}