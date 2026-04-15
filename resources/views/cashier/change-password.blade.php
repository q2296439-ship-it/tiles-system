@php
$layout = match(auth()->user()->role) {
    'admin' => 'layouts.admin',
    'manager' => 'layouts.manager',
    default => 'layouts.cashier',
};
@endphp

@extends($layout)

@section('content')

<style>
.page{
    max-width:700px;
    margin:auto;
}

.card{
    background:#ffffff;
    border-radius:18px;
    padding:28px;
    box-shadow:0 10px 25px rgba(0,0,0,.06);
    border:1px solid #eef2f7;
}

.title{
    font-size:28px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:8px;
}

.subtitle{
    color:#64748b;
    font-size:14px;
    margin-bottom:22px;
}

.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    font-weight:600;
}

.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
    font-weight:600;
}

.form-group{
    margin-bottom:18px;
}

.label{
    display:block;
    font-size:14px;
    font-weight:700;
    margin-bottom:8px;
    color:#111827;
}

.input{
    width:100%;
    padding:12px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:14px;
    background:#fff;
}

.input:focus{
    outline:none;
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.12);
}

.btn{
    border:none;
    padding:12px 18px;
    border-radius:12px;
    background:#2563eb;
    color:#fff;
    font-weight:700;
    cursor:pointer;
    min-width:180px;
}

.btn:hover{
    background:#1d4ed8;
}
</style>

<div class="page">
    <div class="card">

        <div class="title">🔑 Change Password</div>
        <div class="subtitle">
            Update your account password securely.
        </div>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('cashier.password.update') }}">
            @csrf

            <div class="form-group">
                <label class="label">Current Password</label>
                <input type="password" name="current_password" class="input" required>
            </div>

            <div class="form-group">
                <label class="label">New Password</label>
                <input type="password" name="password" class="input" required>
            </div>

            <div class="form-group">
                <label class="label">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="input" required>
            </div>

            <button type="submit" class="btn">
                💾 Update Password
            </button>
        </form>

    </div>
</div>

@endsection