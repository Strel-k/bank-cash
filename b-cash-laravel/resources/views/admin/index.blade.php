@extends('layouts.app')

@section('title', 'Admin Dashboard - B-Cash')

@section('content')
<div class="admin-dashboard">
    <h1>Admin Dashboard</h1>
    <p>Welcome, {{ auth()->user()->full_name ?? 'Admin' }}!</p>
    <ul>
        <li><a href="{{ route('admin.verifications') }}">Pending Verifications</a></li>
    </ul>
</div>
@endsection