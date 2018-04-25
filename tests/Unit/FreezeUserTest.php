<?php

namespace Tests\Unit;

use App\Jobs\FreezeUser;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class FreezeUserTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @dataProvider dataProvider
     * @param $subDays
     * @param $frozen
     */
    public function testHandle($subDays, $frozen)
    {
        $user = factory(User::class)->create([
            'created_at' => Carbon::now()->subDays($subDays)
        ]);

        /** @var FreezeUser $job */
        $job = app(FreezeUser::class, compact('user'));

        $job->handle();

        $this->assertDatabaseHas(
            (new User)->getTable(),
            [
                'email' => $user->email,
                'frozen' => $frozen,
            ]
        );
    }

    public function dataProvider()
    {
        return [
            // [subDays, frozen]
            'Frozen' => [8, true],
            'Not Frozen' => [6, false],
        ];
    }
}
