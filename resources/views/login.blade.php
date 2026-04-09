<!DOCTYPE html>
<html>
<head>
    <title>Tiles System</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            height: 100vh;
            display: flex;
        }

        /* LEFT SIDE */
        .left {
            width: 50%;
            background: linear-gradient(135deg, #1e3a8a, #2563eb);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
        }

        .left h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .left p {
            font-size: 14px;
            opacity: 0.9;
            max-width: 400px;
        }

        /* RIGHT SIDE */
        .right {
            width: 50%;
            background: #0f172a;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            width: 350px;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(20px);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
        }

        .logo {
            text-align: center;
            color: #e2e8f0;
            margin-bottom: 5px;
            font-size: 18px;
            font-weight: 600;
        }

        h2 {
            text-align: center;
            color: white;
            margin-bottom: 25px;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: none;
            background: rgba(255,255,255,0.1);
            color: white;
        }

        input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #2563eb;
        }

        .remember {
            font-size: 12px;
            color: #cbd5e1;
            margin-bottom: 15px;
        }

        .btn {
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .error {
            color: #f87171;
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            margin-top: 15px;
        }

        /* Responsive */
        @media(max-width: 768px){
            .left { display: none; }
            .right { width: 100%; }
        }

    </style>
</head>

<body>

<!-- LEFT SIDE -->
<div class="left">
    <h1>Nicole Tile Center</h1>
    <p>
        Manage your inventory, sales, and reports efficiently with our smart ERP system.
    </p>
</div>

<!-- RIGHT SIDE -->
<div class="right">

    <div class="card">

        <div class="logo">Tile Inventory System</div>
        <h2>Login</h2>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <input type="text" name="username" placeholder="Username" required>

            <input type="password" name="password" placeholder="Password" required>

            <div class="remember">
                <input type="checkbox"> Remember me
            </div>

            <button class="btn">Login</button>

        </form>

        <div class="footer">
            © 2026 Tiles Inventory System
        </div>

    </div>

</div>

</body>
</html>