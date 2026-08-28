<x-admin-layout title="New Coupon">
    <form method="POST" action="{{ route('admin.coupons.store') }}">
        @csrf
        @include('admin.coupons._form', ['coupon' => null])
    </form>
</x-admin-layout>
