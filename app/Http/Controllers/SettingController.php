<?php

namespace App\Http\Controllers;

use App\Helpers\AppSetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = AppSetting::all();

        return view('pages.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_slogan' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string|max:255',
            'logo_light' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'logo_dark' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,webp,ico|max:1024',
        ]);

        $settings = [
            'app_name' => trim($validated['app_name']),
            'app_slogan' => ! empty($validated['app_slogan']) ? trim($validated['app_slogan']) : '',
            'company_name' => ! empty($validated['company_name']) ? trim($validated['company_name']) : '',
            'footer_text' => ! empty($validated['footer_text']) ? trim($validated['footer_text']) : '',
        ];

        if ($request->hasFile('logo_light')) {
            $path = $request->file('logo_light')->store('branding', 'public');
            $settings['logo_light'] = '/storage/'.$path;
        }

        if ($request->hasFile('logo_dark')) {
            $path = $request->file('logo_dark')->store('branding', 'public');
            $settings['logo_dark'] = '/storage/'.$path;
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('branding', 'public');
            $settings['favicon'] = '/storage/'.$path;
        }

        AppSetting::set($settings);

        return redirect()->route('settings.index')->with('success', 'Application settings and branding updated successfully.');
    }
}
