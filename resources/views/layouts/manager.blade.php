<!DOCTYPE html>
<html>
<head>
    <title>Manager Panel</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            display: flex;
        }

        .sidebar {
            width: 220px;
            background: #1e293b;
            color: white;
            height: 100vh;
            padding: 20px;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: #cbd5f5;
            text-decoration: none;
            margin-bottom: 10px;
        }

        .sidebar a:hover {
            color: white;
        }

        .content {
            flex: 1;
            padding: 20px;
            background: #f1f5f9;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h2>Manager Panel</h2>

    <a href="/admin/manager/approvals">📥 Approvals</a>
    <a href="/logout">🚪 Logout</a>
</div>

<div class="content">
    @yield('content')
</div>

</body>
</html>