<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->customers()->withCount('orders')->withSum('orders', 'total');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('q')) {
            $query->where(fn ($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('admin.customers.index', ['customers' => $customers]);
    }

    public function show(User $customer): View
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        $customer->loadCount('orders')->loadSum('orders', 'total');
        $orders = $customer->orders()->latest()->limit(20)->get();
        $addresses = $customer->addresses()->orderByDesc('is_default')->get();

        return view('admin.customers.show', compact('customer', 'orders', 'addresses'));
    }

    public function updateStatus(Request $request, User $customer): RedirectResponse
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        $request->validate(['status' => ['required', 'in:active,disabled']]);
        $customer->update(['status' => UserStatus::from($request->input('status'))]);

        return back()->with('status', 'Customer status updated.');
    }
}
