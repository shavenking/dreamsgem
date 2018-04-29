<?php

namespace App\Listeners;

use App\Events\ShouldCreateOperationHistory;
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
            'user_id' => $event->getOperator(),
            'type' => $event->getType(),
            'result_data' => $operatable->jsonSerialize(),
        ]);
    }
}
