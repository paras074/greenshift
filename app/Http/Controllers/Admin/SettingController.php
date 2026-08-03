<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class SettingController extends Controller
{
    protected $sensitiveKeys = ['pusher_app_secret', 'sendgrid_api_key'];

    public function index()
    {
        $this->authorize('view settings');
        $settings = CoreSetting::pluck('setting_value', 'setting_key')->all();

        // Decrypt only the sensitive ones
        foreach ($this->sensitiveKeys as $key) {
            if (isset($settings[$key]) && !empty($settings[$key])) {
                try {
                    $settings[$key] = Crypt::decryptString($settings[$key]);
                } catch (\Exception $e) {
                    
                }
            }
        }
        return view('settings.main-settings', compact('settings'));
    }

    public function store(Request $request)
    {
        $this->authorize('edit settings');
        $data = $request->except('_token');
        $data['excluded_statuses'] = $request->input('excluded_statuses', []);
        foreach ($data as $key => $value) {
            $valueToStore = $value;
            if (in_array($key, $this->sensitiveKeys) && !empty($value)) {
                $valueToStore = Crypt::encryptString($value);
            }
            CoreSetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $valueToStore]
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }
}