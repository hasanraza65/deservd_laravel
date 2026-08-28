<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BoxOption;
use App\Models\Setting;
use App\Models\ShippingMethod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const DEFINITIONS = [
        'store_name' => 'string',
        'store_email' => 'string',
        'store_phone_primary' => 'string',
        'store_phone_secondary' => 'string',
        'currency' => 'string',
        'tax_rate_percent' => 'integer',
        'free_shipping_threshold_cents' => 'integer',
        'local_pickup_enabled' => 'boolean',
        'store_open' => 'boolean',
    ];

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => Setting::allCast(),
            'shippingMethods' => ShippingMethod::orderBy('sort_order')->get(),
            'boxOptions' => BoxOption::orderBy('sort_order')->get(),
        ]);
    }

    public function updateShippingMethod(Request $request, ShippingMethod $shippingMethod): RedirectResponse
    {
        $request->validate(['price' => ['required', 'numeric', 'min:0', 'max:999.99']]);

        $shippingMethod->update([
            'price' => $request->float('price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', "{$shippingMethod->name} updated.");
    }

    public function updateBoxOption(Request $request, BoxOption $boxOption): RedirectResponse
    {
        $request->validate(['price' => ['required', 'numeric', 'min:0', 'max:999.99']]);

        $boxOption->update([
            'price' => $request->float('price'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', "{$boxOption->size}-cookie box updated.");
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(array_fill_keys(array_keys(self::DEFINITIONS), ['sometimes']));

        foreach (self::DEFINITIONS as $key => $type) {
            $value = $type === 'boolean' ? $request->boolean($key) : $request->input($key);
            Setting::set($key, $value, $type);
        }

        return back()->with('status', 'Settings updated successfully.');
    }
}
