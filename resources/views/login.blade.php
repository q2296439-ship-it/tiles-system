<!DOCTYPE html>
<html>
<head>
    <title>Nicole Tile System</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            height: 100vh;
            display: flex;
            overflow: hidden;
        }

        .light {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.15;
            z-index: 1;
        }

        .light.blue {
            width: 300px;
            height: 300px;
            background: #3b82f6;
            top: 10%;
            left: 60%;
            animation: float 10s ease-in-out infinite;
        }

        .light.purple {
            width: 250px;
            height: 250px;
            background: #6366f1;
            bottom: 10%;
            right: 10%;
            animation: float 14s ease-in-out infinite;
        }

        @keyframes float {
            0%,100% { transform: translateY(0px); }
            50% { transform: translateY(-30px); }
        }

        .left {
            width: 50%;
            background: linear-gradient(135deg, #1e40af, #3b82f6, #2563eb);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
            z-index: 2;
        }

        .left h1 {
            font-size: 34px;
            margin-bottom: 12px;
        }

        .left p {
            font-size: 14px;
            max-width: 420px;
            line-height: 1.6;
            color: rgba(255,255,255,0.85);
        }

        .right {
            width: 50%;
            background: linear-gradient(270deg, #0b1220, #1e293b, #0b1220);
            background-size: 400% 400%;
            animation: gradientMove 12s ease infinite;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .card {
            width: 360px;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(30px);
            padding: 35px;
            border-radius: 18px;
            box-shadow: 
                0 20px 60px rgba(0,0,0,0.7),
                0 0 40px rgba(59,130,246,0.15);
            position: relative;
            z-index: 2;
        }

        .logo {
            text-align: center;
            color: #cbd5f5;
            margin-bottom: 5px;
            font-size: 18px;
            font-weight: 600;
        }

        h2 {
            text-align: center;
            color: #f1f5f9;
            margin-bottom: 25px;
        }

        .input-group {
            width: 100%;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 11px 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: none;
            background: rgba(255,255,255,0.06);
            color: white;
            font-size: 13px;
        }

        .input-group input::placeholder {
            color: #94a3b8;
        }

        .input-group input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #3b82f6;
            background: rgba(255,255,255,0.1);
        }

        .toggle-pass {
            position: absolute;
            right: 12px;
            top: 11px;
            cursor: pointer;
            font-size: 12px;
            color: #94a3b8;
        }

        .form-options {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #cbd5e1;
            cursor: pointer;
        }

        .forgot {
            font-size: 12px;
            color: #93c5fd;
            text-decoration: none;
        }

        .btn {
            width: 100%;
            padding: 11px;
            border-radius: 10px;
            font-size: 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .error {
            color: #f87171;
            text-align: center;
            font-size: 12px;
            margin-bottom: 12px;
        }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #64748b;
            margin-top: 15px;
        }
    </style>
</head>

<body>

<div class="light blue"></div>
<div class="light purple"></div>

<div class="left">
    <h1>Nicole Tile Center</h1>
    <p>Manage your inventory, sales, and reports efficiently with our smart ERP system.</p>
</div>

<div class="right">

    <div class="card">

        <div class="logo">Tile Inventory System</div>
        <h2>Welcome Back</h2>

        {{-- 🔥 FIXED ERROR DISPLAY --}}
        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <div class="input-group">
                <input type="text" name="username" placeholder="Email" required>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span class="toggle-pass" onclick="togglePassword()">👁</span>
            </div>

            <div class="form-options">
                <label class="remember">
                    <input type="checkbox" name="remember">
                    <span>Remember me</span>
                </label>

                <a href="#" class="forgot">Forgot?</a>
            </div>

            <button type="submit" class="btn">Login</button>

        </form>

        <div class="footer">
            © 2026 Tiles Inventory System
        </div>

    </div>

</div>

<script>
function togglePassword() {
    const pass = document.getElementById("password");
    pass.type = pass.type === "password" ? "text" : "password";
}
</script>

</body>
</html>