<?php

namespace App\Http\Controllers\Admin\helpdesk;

use App\Http\Controllers\Controller;
use App\Model\helpdesk\Utility\IpsType;
use Exception;
use Illuminate\Http\Request;

class IpsTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('roles');
    }

    public function index()
    {
        try {
            $ipsTypes = IpsType::orderBy('sort')->get();

            return view('themes.default1.admin.helpdesk.manage.ips-types.index', compact('ipsTypes'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function create()
    {
        try {
            return view('themes.default1.admin.helpdesk.manage.ips-types.create');
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort' => 'required|integer',
        ]);

        try {
            $ipsType = new IpsType();
            $ipsType->fill($request->input())->save();

            return redirect('ips-types')->with('success', 'Jenis IPS berjaya ditambah.');
        } catch (Exception $e) {
            return redirect('ips-types')->with('fails', 'Jenis IPS tidak dapat ditambah.<li>'.$e->getMessage().'</li>');
        }
    }

    public function edit($id)
    {
        try {
            $ipsType = IpsType::whereId($id)->first();

            return view('themes.default1.admin.helpdesk.manage.ips-types.edit', compact('ipsType'));
        } catch (Exception $e) {
            return redirect()->back()->with('fails', $e->getMessage());
        }
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sort' => 'required|integer',
        ]);

        try {
            $ipsType = IpsType::whereId($id)->first();
            $ipsType->fill($request->input())->save();

            return redirect('ips-types')->with('success', 'Jenis IPS berjaya dikemaskini.');
        } catch (Exception $e) {
            return redirect('ips-types')->with('fails', 'Jenis IPS tidak dapat dikemaskini.<li>'.$e->getMessage().'</li>');
        }
    }

    public function destroy($id)
    {
        try {
            $ipsType = IpsType::whereId($id)->first();
            $ipsType->delete();

            return redirect('ips-types')->with('success', 'Jenis IPS berjaya dipadam.');
        } catch (Exception $e) {
            return redirect('ips-types')->with('fails', 'Jenis IPS tidak dapat dipadam.<li>'.$e->getMessage().'</li>');
        }
    }
}
