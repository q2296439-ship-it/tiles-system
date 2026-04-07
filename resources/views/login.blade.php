<!DOCTYPE html>
<html>
<head>
    <title>Tiles System</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        .light {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
        }

        .light.blue {
            width: 300px;
            height: 300px;
            background: #2563eb;
            top: 15%;
            left: 20%;
            animation: float 12s ease-in-out infinite;
        }

        .light.purple {
            width: 250px;
            height: 250px;
            background: #7c3aed;
            bottom: 15%;
            right: 20%;
            animation: float 15s ease-in-out infinite;
        }

        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50% { transform: translateY(-25px); }
        }

        .card {
            position: relative;
            width: 320px;
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(20px);
            padding: 30px 25px;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
            z-index: 2;
            animation: fadeIn 0.8s ease;
        }

        .logo {
            text-align: center;
            margin-bottom: 15px;
            color: #e2e8f0;
            font-size: 16px;
            font-weight: 600;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: white;
            font-size: 18px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        input {
            width: 85%;
            padding: 8px 10px;
            margin-bottom: 12px;
            border-radius: 5px;
            border: none;
            font-size: 12px;
        }

        input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #2563eb;
        }

        .remember {
            width: 85%;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            margin-bottom: 12px;
            color: #cbd5e1;
            font-size: 12px;
        }

        .remember input {
            width: auto;
            margin-right: 5px;
        }

        .btn {
            width: 85%;
            padding: 9px;
            border-radius: 5px;
            font-size: 13px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(37,99,235,0.4);
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
            color: #cbd5e1;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error {
            color: #f87171;
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>

<div class="light blue"></div>
<div class="light purple"></div>

<div class="card">

    <div class="logo">Nicole Tile Center</div>

    <h2>Login</h2>

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="/login">
        @csrf

        <div class="form-group">

            <input type="text" name="username" placeholder="Username" required>

            <input type="password" name="password" placeholder="Password" required>

            <div class="remember">
                <input type="checkbox" id="remember">
                <label for="remember">Remember me</label>
            </div>

            <button class="btn">Login</button>

        </div>
    </form>

    <div class="footer">
        © 2026 Tiles Inventory System
    </div>

</div>

</body>
</html>