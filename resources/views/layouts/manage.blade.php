@extends('layouts.admin')

@section('content')

<style>
.container {
    max-width: 1100px;
    margin: auto;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

.table th {
    background: #f9fafb;
    text-align: left;
    font-size: 14px;
}

.table th, .table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}

.badge {
    padding: 5px 10px;
    border-radius: 8px;
    font-size: 12px;
    color: white;
}

.admin { background: #ef4444; }
.manager { background: #3b82f6; }
.cashier { background: #22c55e; }
.audit { background: #f59e0b; }

</style>

<div class="container">

    <h2 style="margin-bottom:20px;">👥 Manage Accounts</h2>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div style="background:#d1fae5;padding:12px;margin-bottom:15px;border-radius:6px;">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">

        <h3>User List</h3>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Branch</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                <tr>

                    <td>{{ $user->id }}</td>

                    <td>{{ $user->username }}</td>

                    <td>{{ $user->email }}</td>

                    <td>
                        @if($user->role == 'admin')
                            <span class="badge admin">Admin</span>
                        @elseif($user->role == 'branch_manager')
                            <span class="badge manager">Manager</span>
                        @elseif($user->role == 'cashier')
                            <span class="badge cashier">Cashier</span>
                        @elseif($user->role == 'audit')
                            <span class="badge audit">Audit</span>
                        @endif
                    </td>

                    <td>{{ $user->branch->name ?? '—' }}</td>

                    <td>{{ $user->created_at->format('Y-m-d') }}</td>

                </tr>
                @empty
                <tr>
                    <td colspan="6">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@endsection