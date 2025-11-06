@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white p-8 rounded shadow">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold">Welcome to B-Cash</h1>
            <p class="text-sm text-gray-500">Log in or create an account to get started.</p>
        </div>

        <div class="space-y-3">
            <a href="{{ route('login') }}" class="w-full inline-block text-center px-4 py-2 bg-blue-600 text-white rounded">Login</a>
            <a href="{{ route('register') }}" class="w-full inline-block text-center px-4 py-2 border border-gray-300 rounded">Register</a>
        </div>

        <p class="text-xs text-gray-400 text-center mt-4">© {{ date('Y') }} B-Cash</p>
    </div>
</div>
@endsection
