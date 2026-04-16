<!DOCTYPE html>
<html>
<head>
    <title>Nicole Tile System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        *{
            box-sizing:border-box;
            margin:0;
            padding:0;
        }

        body{
            font-family:'Segoe UI',Tahoma,sans-serif;
            min-height:100vh;
            display:flex;
            overflow:hidden;
            background:#0f172a;
        }

        .light{
            position:absolute;
            border-radius:50%;
            filter:blur(120px);
            opacity:.15;
            z-index:1;
        }

        .light.blue{
            width:300px;
            height:300px;
            background:#3b82f6;
            top:10%;
            left:60%;
            animation:float 10s ease-in-out infinite;
        }

        .light.purple{
            width:250px;
            height:250px;
            background:#6366f1;
            bottom:10%;
            right:10%;
            animation:float 14s ease-in-out infinite;
        }

        @keyframes float{
            0%,100%{transform:translateY(0);}
            50%{transform:translateY(-30px);}
        }

        .left,
        .right{
            width:50%;
            min-height:100vh;
            position:relative;
            z-index:2;
        }

        .left{
            background:linear-gradient(135deg,#1e40af,#3b82f6,#2563eb);
            color:#fff;
            display:flex;
            flex-direction:column;
            justify-content:center;
            padding:60px;
        }

        .left h1{
            font-size:38px;
            margin-bottom:14px;
        }

        .left p{
            font-size:15px;
            max-width:420px;
            line-height:1.7;
            color:rgba(255,255,255,.85);
        }

        .right{
            background:linear-gradient(270deg,#0b1220,#1e293b,#0b1220);
            background-size:400% 400%;
            animation:gradientMove 12s ease infinite;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:20px;
        }

        @keyframes gradientMove{
            0%{background-position:0% 50%;}
            50%{background-position:100% 50%;}
            100%{background-position:0% 50%;}
        }

        .card{
            width:100%;
            max-width:380px;
            background:rgba(255,255,255,.04);
            backdrop-filter:blur(30px);
            padding:32px;
            border-radius:18px;
            box-shadow:
                0 20px 60px rgba(0,0,0,.7),
                0 0 40px rgba(59,130,246,.15);
        }

        .logo{
            text-align:center;
            color:#cbd5f5;
            font-size:17px;
            font-weight:600;
            margin-bottom:6px;
        }

        h2{
            text-align:center;
            color:#f8fafc;
            margin-bottom:24px;
            font-size:28px;
        }

        .error{
            color:#f87171;
            text-align:center;
            font-size:12px;
            margin-bottom:14px;
        }

        .input-group{
            position:relative;
            margin-bottom:15px;
        }

        .input-group input{
            width:100%;
            padding:12px 42px 12px 12px;
            border:none;
            border-radius:10px;
            background:rgba(255,255,255,.06);
            color:#fff;
            font-size:14px;
        }

        .input-group input::placeholder{
            color:#94a3b8;
        }

        .input-group input:focus{
            outline:none;
            box-shadow:0 0 0 2px #3b82f6;
            background:rgba(255,255,255,.08);
        }

        .toggle-pass{
            position:absolute;
            right:12px;
            top:12px;
            cursor:pointer;
            color:#94a3b8;
            font-size:13px;
        }

        .form-options{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
            margin-bottom:18px;
            flex-wrap:wrap;
        }

        .remember{
            display:flex;
            align-items:center;
            gap:6px;
            color:#cbd5e1;
            font-size:12px;
        }

        .forgot{
            font-size:12px;
            color:#93c5fd;
            text-decoration:none;
        }

        .btn{
            width:100%;
            padding:12px;
            border:none;
            border-radius:10px;
            background:linear-gradient(135deg,#3b82f6,#2563eb);
            color:#fff;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
        }

        .footer{
            text-align:center;
            font-size:11px;
            color:#64748b;
            margin-top:16px;
        }

        /* MOBILE */
        @media(max-width:900px){
            body{
                flex-direction:column;
                overflow:auto;
            }

            .left,
            .right{
                width:100%;
                min-height:auto;
            }

            .left{
                padding:40px 24px;
                text-align:center;
                align-items:center;
            }

            .left h1{
                font-size:30px;
            }

            .left p{
                max-width:100%;
            }

            .right{
                padding:24px 16px 40px;
            }
        }

        @media(max-width:480px){
            .card{
                padding:24px 18px;
            }

            h2{
                font-size:24px;
            }

            .left h1{
                font-size:26px;
            }

            .left p{
                font-size:14px;
            }
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

        @if($errors->any())
            <div class="error">{{ $errors->first() }}</div>
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
function togglePassword(){
    const pass=document.getElementById('password');
    pass.type=pass.type==='password' ? 'text' : 'password';
}
</script>

</body>
</html>