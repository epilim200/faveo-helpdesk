<?php

namespace App\Http\Controllers\Admin\helpdesk;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Utility\State;
use Exception;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('roles');
    }

    public function index()
    {
        try {
            $states = State::withCount('districts')->orderBy('code')->get();

            return view('themes.default1.admin.helpdesk.manage.states.index', compact('states'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            return view('themes.default1.admin.helpdesk.manage.states.create');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:2|unique:states,code',
            'name' => 'required|string|max:255',
        ]);

        try {
            $state = new State();
            $state->fill($request->input())->save();

            return redirect('states')->with('success', 'Negeri berjaya ditambah.');
        } catch (Exception $e) {
            return redirect('states')->with('fails', 'Negeri tidak dapat ditambah.<li>'.$e->getMessage().'</li>');
        }
    }

    public function edit($id)
    {
        try {
            $state = State::whereId($id)->first();

            return view('themes.default1.admin.helpdesk.manage.states.edit', compact('state'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:2|unique:states,code,'.$id,
            'name' => 'required|string|max:255',
        ]);

        try {
            $state = State::whereId($id)->first();
            $state->fill($request->input())->save();

            return redirect('states')->with('success', 'Negeri berjaya dikemaskini.');
        } catch (Exception $e) {
            return redirect('states')->with('fails', 'Negeri tidak dapat dikemaskini.<li>'.$e->getMessage().'</li>');
        }
    }

    public function destroy($id)
    {
        try {
            $state = State::whereId($id)->first();
            $state->delete();

            return redirect('states')->with('success', 'Negeri berjaya dipadam.');
        } catch (Exception $e) {
            return redirect('states')->with('fails', 'Negeri tidak dapat dipadam.<li>'.$e->getMessage().'</li>');
        }
    }
}
