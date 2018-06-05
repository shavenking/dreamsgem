<?php

namespace App\Listeners;

use App\Events\ShouldCreateOperationHistory;
use App\Events\SubTypeAware;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CreateOperationHistory
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param ShouldCreateOperationHistory $event
     * @return void
     */
    public function handle(ShouldCreateOperationHistory $event)
    {
        $operatable = $event->getOperatable();
        $operatable->operationHistories()->create([
            'operator_id' => optional($event->getOperator())->id,
            'user_id' => optional($event->getUser())->id,
            'type' => $event->getType(),
            'sub_type' => $event instanceof SubTypeAware ? $event->subType() : null,
            'result_data' => $operatable->getAttributes(),
            'delta' => $event->getDelta(),
        ]);
    }
}
