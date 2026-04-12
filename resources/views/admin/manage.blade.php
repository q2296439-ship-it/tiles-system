@extends('layouts.admin')

@section('content')

<style>
.container { max-width: 1200px; margin:auto; }
.card {
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}
.table { width:100%; border-collapse:collapse; }

.table th, .table td { 
    padding:12px; 
    border-bottom:1px solid #eee; 
}

input, select {
    padding:6px;
    border:1px solid #ddd;
    border-radius:6px;
    width:100%;
    box-sizing: border-box;
}

.btn {
    padding:6px 10px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    color:white;
}

.save { background:#16a34a; }
.delete { background:#dc2626; }

/* 🔥 ALIGNMENT FIX */
.table th:nth-child(1),
.table td:nth-child(1) { width: 25%; }

.table th:nth-child(2),
.table td:nth-child(2) { width: 25%; }

.table th:nth-child(3),
.table td:nth-child(3) { width: 15%; }

.table th:nth-child(4),
.table td:nth-child(4) { width: 20%; }

.table th:nth-child(5),
.table td:nth-child(5) { width: 15%; }

.table th:nth-child(6),
.table td:nth-child(6) { width: 20%; text-align:center; }

.table td { vertical-align: middle; }

/* 🔥 BUTTON ALIGN */
.action-buttons {
    display:flex;
    gap:5px;
    justify-content:center;
}
</style>

<div class="container">

<h2>👥 Manage Accounts</h2>

{{-- USERS --}}
<div class="card">

<h3>Users</h3>

<table class="table">
<thead>
<tr>
    <th>Username</th>
    <th>Email</th>
    <th>Role</th>
    <th>Branch</th>
    <th>Password</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
@foreach($users as $user)
<tr>

<form method="POST" action="/admin/users/update/{{ $user->id }}">
@csrf

<td>
<input type="text" name="username" value="{{ $user->username }}">
</td>

<td>
<input type="email" name="email" value="{{ $user->email }}">
</td>

<td>
<select name="role">
    <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
    <option value="branch_manager" {{ $user->role=='branch_manager'?'selected':'' }}>Manager</option>
    <option value="cashier" {{ $user->role=='cashier'?'selected':'' }}>Cashier</option>
    <option value="audit" {{ $user->role=='audit'?'selected':'' }}>Audit</option>
</select>
</td>

<td>
<select name="branch_id">
@foreach($branches as $branch)
<option value="{{ $branch->id }}" {{ $user->branch_id == $branch->id ? 'selected':'' }}>
{{ $branch->name }}
</option>
@endforeach
</select>
</td>

<td>
<input type="password" name="password" placeholder="New password (optional)">
</td>

<td class="action-buttons">

<button class="btn save">Save</button>
</form>

<form method="POST" action="/admin/users/delete/{{ $user->id }}">
@csrf
<button class="btn delete" onclick="return confirm('Delete user?')">Delete</button>
</form>

</td>

</tr>
@endforeach
</tbody>
</table>

</div>

{{-- BRANCHES --}}
<div class="card">

<h3>Branches</h3>

<table class="table">
<thead>
<tr>
    <th>Name</th>
    <th>Address</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
@foreach($branches as $branch)
<tr>

<form method="POST" action="/admin/branches/update/{{ $branch->id }}">
@csrf

<td>
<input type="text" name="name" value="{{ $branch->name }}">
</td>

<td>
<input type="text" name="address" value="{{ $branch->address }}">
</td>

<td class="action-buttons">

<button class="btn save">Save</button>
</form>

<form method="POST" action="/admin/branches/delete/{{ $branch->id }}">
@csrf
<button class="btn delete" onclick="return confirm('Delete branch?')">Delete</button>
</form>

</td>

</tr>
@endforeach
</tbody>
</table>

</div>

</div>

@endsection