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
            background: linear-gradient(135deg, #1e40af, #3b82f6, #2563eb);
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px;
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

        /* RIGHT SIDE */
        .right {
            width: 50%;
            background: radial-gradient(circle at top right, #1e293b, #0b1220);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* CARD */
        .card {
            width: 360px;
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(30px);
            padding: 35px;
            border-radius: 18px;
            box-shadow: 
                0 20px 60px rgba(0,0,0,0.7),
                0 0 40px rgba(59,130,246,0.15);
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

        /* INPUT */
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

        /* SHOW PASSWORD */
        .toggle-pass {
            position: absolute;
            right: 12px;
            top: 11px;
            cursor: pointer;
            font-size: 12px;
            color: #94a3b8;
        }

        /* OPTIONS */
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

        .remember input {
            accent-color: #3b82f6;
            transform: scale(1.05);
        }

        .forgot {
            font-size: 12px;
            color: #93c5fd;
            text-decoration: none;
            transition: 0.2s;
        }

        .forgot:hover {
            color: white;
        }

        /* BUTTON */
        .btn {
            width: 100%;
            padding: 11px;
            border-radius: 10px;
            font-size: 14px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.25s;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(59,130,246,0.4);
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

        /* RESPONSIVE */
        @media(max-width: 768px){
            .left { display: none; }
            .right { width: 100%; }
        }
    </style>
</head>

<body>

<!-- LEFT -->
<div class="left">
    <h1>Nicole Tile Center</h1>
    <p>
        Manage your inventory, sales, and reports efficiently with our smart ERP system.
    </p>
</div>

<!-- RIGHT -->
<div class="right">

    <div class="card">

        <div class="logo">Tile Inventory System</div>
        <h2>Welcome Back</h2>

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <form method="POST" action="/login">
            @csrf

            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" placeholder="Password" required>
                <span class="toggle-pass" onclick="togglePassword()">👁</span>
            </div>

            <div class="form-options">
                <label class="remember">
                    <input type="checkbox">
                    <span>Remember me</span>
                </label>

                <a href="#" class="forgot">Forgot?</a>
            </div>

            <button class="btn">Login</button>

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