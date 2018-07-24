<?php

namespace App\Http\Controllers\Admin;

use App\Dragon;
use App\Events\DragonCreated;
use App\User;
use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserActivationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dragons = Dragon::whereType(Dragon::TYPE_SMALL)->with('owner', 'user')->get();

        return view('admin.user-activations.create', compact('dragons'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws Exception
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
        ]);

        $user = User::member()->findOrFail($request->user_id);

        abort_if(
            $user->activatedDragon,
            Response::HTTP_BAD_REQUEST,
            trans('errors.User has been activated')
        );


        DB::beginTransaction();

        try {
            $dragon = $user->dragons()->create(['type' => Dragon::TYPE_SMALL]);

            event(new DragonCreated($dragon));

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()->route('admin.user-activations.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
