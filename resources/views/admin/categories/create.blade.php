<x-admin-layout title="New Category">
    <form method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        @include('admin.categories._form', ['category' => null])
    </form>
</x-admin-layout>
