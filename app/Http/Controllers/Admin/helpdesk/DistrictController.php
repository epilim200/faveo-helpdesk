<?php

namespace App\Http\Controllers\Admin\helpdesk;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Utility\District;
use App\Model\helpdesk\Utility\State;
use Exception;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('roles');
    }

    public function index()
    {
        try {
            $districts = District::with('state')->orderBy('code')->get();

            return view('themes.default1.admin.helpdesk.manage.districts.index', compact('districts'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            $states = State::orderBy('name')->pluck('name', 'id');

            return view('themes.default1.admin.helpdesk.manage.districts.create', compact('states'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:2',
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
        ]);

        try {
            $district = new District();
            $district->fill($request->input())->save();

            return redirect('districts')->with('success', 'Daerah berjaya ditambah.');
        } catch (Exception $e) {
            return redirect('districts')->with('fails', 'Daerah tidak dapat ditambah.<li>'.$e->getMessage().'</li>');
        }
    }

    public function edit($id)
    {
        try {
            $district = District::whereId($id)->first();
            $states = State::orderBy('name')->pluck('name', 'id');

            return view('themes.default1.admin.helpdesk.manage.districts.edit', compact('district', 'states'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:2',
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id',
        ]);

        try {
            $district = District::whereId($id)->first();
            $district->fill($request->input())->save();

            return redirect('districts')->with('success', 'Daerah berjaya dikemaskini.');
        } catch (Exception $e) {
            return redirect('districts')->with('fails', 'Daerah tidak dapat dikemaskini.<li>'.$e->getMessage().'</li>');
        }
    }

    public function destroy($id)
    {
        try {
            $district = District::whereId($id)->first();
            $district->delete();

            return redirect('districts')->with('success', 'Daerah berjaya dipadam.');
        } catch (Exception $e) {
            return redirect('districts')->with('fails', 'Daerah tidak dapat dipadam.<li>'.$e->getMessage().'</li>');
        }
    }

    public function byState($stateId)
    {
        $districts = District::where('state_id', $stateId)->orderBy('name')->get(['id', 'code', 'name']);

        return response()->json($districts);
    }
}
