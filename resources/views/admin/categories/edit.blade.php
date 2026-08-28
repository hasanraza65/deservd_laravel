<x-admin-layout title="Edit Category">
    <form method="POST" action="{{ route('admin.categories.update', $category) }}">
        @csrf @method('PUT')
        @include('admin.categories._form', ['category' => $category])
    </form>
</x-admin-layout>
