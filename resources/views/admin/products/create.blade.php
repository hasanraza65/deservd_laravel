<x-admin-layout title="New Product">
    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
        @csrf
        @include('admin.products._form', ['product' => null])
    </form>
</x-admin-layout>
