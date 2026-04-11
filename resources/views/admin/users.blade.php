@extends('layouts.admin')

@section('content')

<style>
.container {
    max-width: 900px;
    margin: auto;
}

.header h2 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.header p {
    color: #6b7280;
    margin-bottom: 20px;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 15px;
}

input, select {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    width: 100%;
    font-size: 14px;
}

button {
    padding: 14px;
    border-radius: 10px;
    border: none;
    background: #2563eb;
    color: white;
    font-weight: bold;
    margin-top: 15px;
    cursor: pointer;
    width: 100%;
}

button:hover {
    background: #1d4ed8;
}
</style>

<div class="container">

    <div class="header">
        <h2>👤 Add User</h2>
        <p>Create new system account</p>
    </div>

    <div class="card">
        <form method="POST" action="/admin/users/store">
            @csrf

            <div class="grid">

                <input type="text" name="username" placeholder="Username" required>

                <input type="email" name="email" placeholder="Email" required>

                <input type="password" name="password" placeholder="Password" required>

                <input type="text" name="employee_name" placeholder="Employee Name" required>

                <input type="text" name="employee_id" placeholder="Employee ID" required>

                <select name="branch_id" required>
                    <option value="">Select Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>

                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin">🔥 Admin</option>
                    <option value="branch_manager">🏬 Branch Manager</option>
                    <option value="cashier">💰 Cashier</option>
                    <option value="audit">📊 Audit</option>
                </select>

            </div>

            <button type="submit">➕ Add User</button>
        </form>
    </div>

</div>

@endsection