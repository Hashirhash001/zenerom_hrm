@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Reset Password</h3>
    <form action="{{ route('reset.password.update') }}" method="POST">
        @csrf
        <!-- Hidden field containing the user ID -->
        <input type="hidden" name="uid" value="{{ $uid }}">
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" name="password" id="password" class="form-control" required minlength="6">
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
</div>
@endsection
