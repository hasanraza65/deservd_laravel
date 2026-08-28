<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use ApiResponses;

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

    public function index(): JsonResponse
    {
        return $this->success(Setting::allCast());
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate(array_fill_keys(array_keys(self::DEFINITIONS), ['sometimes']));

        foreach (self::DEFINITIONS as $key => $type) {
            if ($request->has($key)) {
                Setting::set($key, $request->input($key), $type);
            }
        }

        return $this->success(Setting::allCast(), 'Settings updated successfully.');
    }
}
