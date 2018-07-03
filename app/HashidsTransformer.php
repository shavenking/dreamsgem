<?php

namespace App;

use App\Http\Middleware\ReplaceHashids;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Vinkla\Hashids\HashidsManager;

class HashidsTransformer
{
    public $hashids;

    public function __construct(HashidsManager $hashids)
    {
        $this->hashids = $hashids;
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
            $target->setAttribute('id', $this->hashids->encode((int) $target->getKey()));
        }

        if ($target instanceof OperationHistory) {
            $this->transformOperationHistoryResultData($target);
        }

        foreach (ReplaceHashids::$shouldReplace as $shouldReplaced) {
            if ($target->{$shouldReplaced} && is_int($target->{$shouldReplaced})) {
                $target->setAttribute($shouldReplaced, $this->hashids->encode($target->{$shouldReplaced}));
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
                $resultData->$shouldReplaced = $this->hashids->encode($resultData->$shouldReplaced);
            }
        }

        $operationHistory->result_data = $resultData;
    }
}