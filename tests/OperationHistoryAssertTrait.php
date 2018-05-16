<?php

namespace Tests;

use App\OperatableTrait;
use App\OperationHistory;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\Concerns\InteractsWithDatabase;

trait OperationHistoryAssertTrait
{
    use InteractsWithDatabase;

    public function assertOperationHistoryExists(
        Model $model,
        $type,
        User $operator = null
    ) {
        $operationHistory = OperationHistory::where([
            'operatable_type' => $model->getMorphClass(),
            'operatable_id' => $model->getKey(),
            'user_id' => optional($operator)->id,
            'type' => $type,
        ])->first();

        $this->assertNotNull($operationHistory, 'OperationHistory not exists.');
        foreach ($model->toArray() as $key => $value) {
            $this->assertEquals($value, data_get($operationHistory->result_data, $key), "$key not equals");
        }
    }

    public function assertOperationHistoryNotExists(
        Model $model,
        $type,
        User $operator = null
    ) {
        $operationHistory = OperationHistory::where([
            'operatable_type' => $model->getMorphClass(),
            'operatable_id' => $model->getKey(),
            'user_id' => optional($operator)->id,
            'type' => $type,
        ])->first();

        $this->assertNull($operationHistory, 'OperationHistory should not be created.');
    }
}
