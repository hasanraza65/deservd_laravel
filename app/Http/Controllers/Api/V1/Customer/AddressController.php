<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\AddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $addresses = $request->user()->addresses()->orderByDesc('is_default')->get();

        return $this->success(AddressResource::collection($addresses));
    }

    public function store(AddressRequest $request): JsonResponse
    {
        $address = DB::transaction(function () use ($request) {
            $data = $request->validated();

            if ($data['is_default'] ?? false) {
                $request->user()->addresses()->where('type', $data['type'])->update(['is_default' => false]);
            }

            return $request->user()->addresses()->create($data);
        });

        // DB-level defaults (country, is_default) aren't reflected on the
        // in-memory instance returned by create() — refresh for an accurate response.
        return $this->success(new AddressResource($address->fresh()), 'Address added successfully.', 201);
    }

    public function update(AddressRequest $request, Address $address): JsonResponse
    {
        Gate::authorize('update', $address);

        DB::transaction(function () use ($request, $address) {
            $data = $request->validated();

            if ($data['is_default'] ?? false) {
                $address->user->addresses()->where('type', $data['type'])->where('id', '!=', $address->id)->update(['is_default' => false]);
            }

            $address->update($data);
        });

        return $this->success(new AddressResource($address->fresh()), 'Address updated successfully.');
    }

    public function destroy(Address $address): JsonResponse
    {
        Gate::authorize('delete', $address);

        $address->delete();

        return $this->success(null, 'Address removed successfully.');
    }
}
