<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CustomerController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $query = User::query()
            ->customers()
            ->withCount('orders')
            ->withSum('orders', 'total');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('q')) {
            $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $customers = $query->latest()->paginate($request->integer('per_page', 20));

        return $this->success([
            'items' => CustomerResource::collection($customers->items()),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    public function show(User $customer): JsonResponse
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        $customer->loadCount('orders')->loadSum('orders', 'total');

        return $this->success([
            'customer' => new CustomerResource($customer),
            'orders' => OrderResource::collection($customer->orders()->latest()->limit(20)->get()),
        ]);
    }

    public function updateStatus(Request $request, User $customer): JsonResponse
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        $request->validate(['status' => ['required', new Enum(UserStatus::class)]]);

        $customer->update(['status' => $request->input('status')]);

        return $this->success(new CustomerResource($customer->fresh()), 'Customer status updated.');
    }
}
