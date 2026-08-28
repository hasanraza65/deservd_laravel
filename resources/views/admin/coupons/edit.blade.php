<x-admin-layout title="Edit Coupon">
    <form method="POST" action="{{ route('admin.coupons.update', $coupon) }}">
        @csrf @method('PUT')
        @include('admin.coupons._form', ['coupon' => $coupon])
    </form>
</x-admin-layout>
