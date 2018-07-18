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

    public function __construct(Generator $faker)
    {
        $this->faker = $faker;
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

    private function protect($original)
    {
        $this->faker->seed($original);

        $strPrefix = implode('', [
            $this->faker->randomElement(['A', 'B', 'C']),
            ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'][User::find($original)->created_at->month - 1],
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
            preg_match('/[a-c]{1}[a-l]{1}[a-c]{1}[0-9]{' . self::NUMBER_OF_RANDOM_DIGIT_PREFIX . '}([0-9]+)[0-9]{' . self::NUMBER_OF_RANDOM_DIGIT_POSTFIX . '}/', $protected, $matches)
            && count($matches) === 2
            && $this->protect($matches[1]) === $matches[0]
        ) {
            return $matches[1];
        }

        return '';
    }
}