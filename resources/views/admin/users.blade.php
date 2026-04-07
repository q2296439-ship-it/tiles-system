<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>

    <style>
        body {
            font-family: Arial;
            background: #f1f5f9;
            margin: 0;
            padding: 20px;
        }

        h2 {
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }

        input, select {
            padding: 10px;
            margin-bottom: 10px;
            width: 100%;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        button {
            padding: 10px;
            background: #3b82f6;
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #2563eb;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9fafb;
            text-align: left;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f1f5f9;
        }
    </style>
</head>

<body>

<h2>👥 Manage Users</h2>

<!-- ADD USER -->
<div class="card">
    <h3>Add User</h3>

    <form method="POST" action="/admin/users/store">
        @csrf

        <input type="text" name="username" placeholder="Username" required>

        <input type="email" name="email" placeholder="Email" required>

        <input type="password" name="password" placeholder="Password" required>

        <!-- ROLE -->
        <select name="role" required>
            <option value="admin">🔥 Admin</option>
            <option value="branch_manager">🏬 Branch Manager</option>
            <option value="cashier">💰 Cashier</option>
            <option value="audit">📊 Audit</option>
        </select>

        <!-- BRANCH -->
        <select name="branch_id" required>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>

        <button type="submit">Add User</button>
    </form>
</div>

<!-- USER LIST -->
<div class="card">
    <h3>User List</h3>

    <table>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Branch</th>
        </tr>

        @foreach($users as $user)
        <tr>
            <td>{{ $user->username }}</td>
            <td>{{ $user->email }}</td>

            <td>
                @if($user->role == 'admin')
                    🔥 Admin
                @elseif($user->role == 'branch_manager')
                    🏬 Manager
                @elseif($user->role == 'cashier')
                    💰 Cashier
                @elseif($user->role == 'audit')
                    📊 Audit
                @endif
            </td>

            <td>{{ $user->branch->name ?? 'N/A' }}</td>
        </tr>
        @endforeach
    </table>
</div>

</body>
</html>