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
            font-size: 34px;
            margin-bottom: 12px;
        }

        .left p {
            font-size: 14px;
            opacity: 0.9;
            max-width: 420px;
            line-height: 1.6;
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
            width: 360px;
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(25px);
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 15px 45px rgba(0,0,0,0.6);
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

        /* INPUT GROUP */
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
            background: rgba(255,255,255,0.08);
            color: white;
            font-size: 13px;
        }

        .input-group input::placeholder {
            color: #94a3b8;
        }

        .input-group input:focus {
            outline: none;
            box-shadow: 0 0 0 2px #2563eb;
            background: rgba(255,255,255,0.12);
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
            accent-color: #2563eb;
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
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.25s;
            font-weight: 600;
            letter-spacing: 0.3px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37,99,235,0.5);
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
            color: #94a3b8;
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