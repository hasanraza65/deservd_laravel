<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

/**
 * Public, read-only subset of store settings — just what the storefront
 * needs to render dynamically (free-shipping threshold, contact info). The
 * full settings set (admin's own operational config) stays behind
 * Api\V1\Admin\SettingController.
 */
class SettingController extends Controller
{
    use ApiResponses;

    private const PUBLIC_KEYS = [
        'store_name',
        'store_email',
        'store_phone_primary',
        'store_phone_secondary',
        'currency',
        'free_shipping_threshold_cents',
        'local_pickup_enabled',
        'store_open',
    ];

    public function index(): JsonResponse
    {
        $all = Setting::allCast();

        return $this->success(array_intersect_key($all, array_flip(self::PUBLIC_KEYS)));
    }
}
