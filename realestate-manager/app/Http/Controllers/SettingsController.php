<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->all();
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:150',
            'company_address' => 'required|string|max:250',
            'vendor_name' => 'required|string|max:150',
            'vendor_cnic' => 'required|string|max:15',
        ]);

        foreach ($request->only(['company_name', 'company_address', 'vendor_name', 'vendor_cnic']) as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}
