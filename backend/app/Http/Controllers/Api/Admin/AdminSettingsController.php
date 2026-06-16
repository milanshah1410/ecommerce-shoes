<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSettingsController extends Controller
{
    private string $path = 'app_settings.json';

    private function load(): array
    {
        if (! Storage::disk('local')->exists($this->path)) {
            return $this->defaults();
        }

        return json_decode(Storage::disk('local')->get($this->path), true) ?? $this->defaults();
    }

    private function defaults(): array
    {
        return [
            'store_name'                => config('app.name', 'SoleStore'),
            'store_email'               => '',
            'store_phone'               => '',
            'store_address'             => '',
            'currency'                  => 'INR',
            'currency_symbol'           => '₹',
            'tax_rate'                  => 0,
            'shipping_charge'           => 0,
            'free_shipping_threshold'   => 0,
            'logo'                      => null,
            'razorpay_key'              => '',
            'stripe_key'                => '',
            'smtp_host'                 => '',
            'smtp_port'                 => 587,
            'smtp_user'                 => '',
            'smtp_from_name'            => '',
        ];
    }

    public function show()
    {
        return response()->json($this->load());
    }

    public function update(Request $request)
    {
        $settings = $this->load();

        $fields = ['store_name','store_email','store_phone','store_address','currency','currency_symbol','tax_rate','shipping_charge','free_shipping_threshold','razorpay_key','stripe_key','smtp_host','smtp_port','smtp_user','smtp_from_name'];

        foreach ($fields as $field) {
            if ($request->has($field)) {
                $settings[$field] = $request->input($field);
            }
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('settings', 'public');
            $settings['logo'] = "/storage/{$path}";
        }

        Storage::disk('local')->put($this->path, json_encode($settings, JSON_PRETTY_PRINT));

        return response()->json($settings);
    }
}
