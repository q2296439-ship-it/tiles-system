<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NMC Home Improvement Center</title>

<style>
*{margin:0;padding:0;box-sizing:border-box}

body{
    font-family:'Segoe UI',Tahoma,sans-serif;
    min-height:100vh;
    background:#1f2d6b;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:20px;
    overflow:hidden;
}

.wrapper{
    width:100%;
    max-width:1180px;
    min-height:680px;
    background:#fff;
    border-radius:24px;
    overflow:hidden;
    display:grid;
    grid-template-columns:420px 1fr;
    box-shadow:0 25px 70px rgba(0,0,0,.25);
}

/* LEFT */
.left{
    background:#fff;
    padding:42px 34px;
    display:flex;
    flex-direction:column;
    justify-content:center;
    position:relative;
    z-index:2;
}

.brand{
    font-size:22px;
    font-weight:800;
    color:#1e3a8a;
    margin-bottom:8px;
}

.sub{
    font-size:13px;
    color:#64748b;
    margin-bottom:28px;
}

.logo-wrap{
    text-align:center;
    width:100%;
    margin:0 auto 16px;
}

.login-logo{
    width:90px;
    height:90px;
    object-fit:contain;
    display:block;
    margin:0 auto;
    filter:drop-shadow(0 8px 18px rgba(0,0,0,.12));
}

.title{
    text-align:center;
    font-size:28px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:18px;
}

.error{
    background:#fee2e2;
    color:#b91c1c;
    padding:10px 12px;
    border-radius:10px;
    font-size:13px;
    margin-bottom:14px;
    text-align:center;
}

.input{
    width:100%;
    padding:13px 14px;
    border:1px solid #dbeafe;
    border-radius:12px;
    font-size:14px;
    outline:none;
    margin-bottom:12px;
}

.input:focus{
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,.12);
}

.pass-wrap{position:relative;}

.eye{
    position:absolute;
    right:12px;
    top:14px;
    cursor:pointer;
    color:#64748b;
    font-size:13px;
}

.options{
    display:flex;
    justify-content:space-between;
    gap:10px;
    align-items:center;
    margin:6px 0 16px;
    font-size:12px;
    color:#64748b;
    flex-wrap:wrap;
}

.login-btn{
    width:100%;
    padding:13px;
    border:none;
    border-radius:12px;
    background:linear-gradient(135deg,#1e3a8a,#2563eb);
    color:#fff;
    font-weight:800;
    cursor:pointer;
    font-size:14px;
}

.login-btn:hover{
    transform:translateY(-1px);
}

.footer{
    text-align:center;
    margin-top:16px;
    font-size:12px;
    color:#94a3b8;
}

/* RIGHT */
.right{
    position:relative;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px;
    color:#fff;
    overflow:hidden;
    background:linear-gradient(135deg,#243a8f,#1e3a8a,#10245f,#3156c9);
    background-size:300% 300%;
    animation:bgShift 14s ease infinite;
}

@keyframes bgShift{
    0%{background-position:0% 50%}
    50%{background-position:100% 50%}
    100%{background-position:0% 50%}
}

/* glowing lights */
.blob1,.blob2,.blob3{
    position:absolute;
    border-radius:50%;
    filter:blur(18px);
    opacity:.9;
}

.blob1{
    width:420px;
    height:420px;
    background:radial-gradient(circle,#ffffff,rgba(255,255,255,.05));
    top:-80px;
    right:120px;
    animation:float1 9s ease-in-out infinite;
}

.blob2{
    width:360px;
    height:360px;
    background:radial-gradient(circle,#f8fafc,rgba(255,255,255,.03));
    bottom:-120px;
    left:80px;
    animation:float2 11s ease-in-out infinite;
}

.blob3{
    width:220px;
    height:220px;
    background:radial-gradient(circle,#ffffff,rgba(255,255,255,.02));
    top:45%;
    left:45%;
    animation:float3 7s ease-in-out infinite;
}

@keyframes float1{
    0%,100%{transform:translate(0,0)}
    50%{transform:translate(-35px,28px)}
}

@keyframes float2{
    0%,100%{transform:translate(0,0)}
    50%{transform:translate(30px,-24px)}
}

@keyframes float3{
    0%,100%{transform:translate(0,0) scale(1)}
    50%{transform:translate(-15px,15px) scale(1.08)}
}

/* sparkles */
.sparkles{
    position:absolute;
    inset:0;
    z-index:1;
    overflow:hidden;
}

.sparkles span{
    position:absolute;
    width:6px;
    height:6px;
    border-radius:50%;
    background:rgba(255,255,255,.7);
    box-shadow:0 0 12px rgba(255,255,255,.8);
    animation:rise 10s linear infinite;
}

.sparkles span:nth-child(1){left:8%;bottom:-20px;animation-delay:0s}
.sparkles span:nth-child(2){left:18%;bottom:-40px;animation-delay:2s}
.sparkles span:nth-child(3){left:35%;bottom:-10px;animation-delay:4s}
.sparkles span:nth-child(4){left:52%;bottom:-30px;animation-delay:1s}
.sparkles span:nth-child(5){left:68%;bottom:-25px;animation-delay:5s}
.sparkles span:nth-child(6){left:82%;bottom:-50px;animation-delay:3s}
.sparkles span:nth-child(7){left:92%;bottom:-15px;animation-delay:6s}

@keyframes rise{
    0%{transform:translateY(0) scale(.8);opacity:0}
    10%{opacity:1}
    100%{transform:translateY(-760px) scale(1.4);opacity:0}
}

.content{
    position:relative;
    z-index:2;
    max-width:560px;
}

.welcome{
    font-size:58px;
    font-weight:900;
    line-height:1;
    margin-bottom:10px;
}

.company{
    font-size:30px;
    font-weight:800;
    line-height:1.2;
    margin-bottom:12px;
}

.system{
    font-size:18px;
    color:#dbeafe;
    margin-bottom:22px;
    font-weight:600;
}

.list{
    display:grid;
    gap:10px;
    font-size:15px;
    color:#eff6ff;
}

.list div{
    padding:10px 14px;
    background:rgba(255,255,255,.08);
    border:1px solid rgba(255,255,255,.08);
    border-radius:12px;
    backdrop-filter:blur(10px);
}

.note{
    margin-top:18px;
    color:#cbd5e1;
    font-size:14px;
}

/* MOBILE */
@media(max-width:980px){
    .wrapper{grid-template-columns:1fr}
    .right{min-height:420px}
    .welcome{font-size:42px}
    .company{font-size:24px}
}

@media(max-width:560px){
    body{padding:10px}
    .wrapper{border-radius:18px}
    .left,.right{padding:22px}
    .welcome{font-size:34px}
    .company{font-size:20px}
    .title{font-size:24px}
}
</style>
</head>

<body>

<div class="wrapper">

<div class="left">
    <div class="brand">NMC HOME IMPROVEMENT CENTER</div>
    <div class="sub">Secure ERP Access Portal</div>

    <div class="logo-wrap">
        <img src="/logo.png?v=2" alt="NMC Logo" class="login-logo">
    </div>

    <div class="title">Login</div>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/login">
        @csrf

        <input class="input" type="text" name="username" placeholder="Username" required>

        <div class="pass-wrap">
            <input class="input" id="password" type="password" name="password" placeholder="Password" required>
            <span class="eye" onclick="togglePassword()">👁</span>
        </div>

        <div class="options">
            <label><input type="checkbox" name="remember"> Remember me</label>
            <span>Authorized Users Only</span>
        </div>

        <button class="login-btn" type="submit">LOGIN</button>
    </form>

    <div class="footer">© 2026 Sales & Inventory ERP</div>
</div>

<div class="right">

    <div class="sparkles">
        <span></span><span></span><span></span>
        <span></span><span></span><span></span><span></span>
    </div>

    <div class="blob1"></div>
    <div class="blob2"></div>
    <div class="blob3"></div>

    <div class="content">
        <div class="welcome">Welcome.</div>
        <div class="company">NMC HOME IMPROVEMENT CENTER</div>
        <div class="system">Sales and Inventory System</div>

        <div class="list">
            <div>✔ Real-Time Stock Monitoring</div>
            <div>✔ Sales Tracking & Daily Reports</div>
            <div>✔ Multi-Branch Management</div>
            <div>✔ Secure Role-Based Access</div>
            <div>✔ Fast, Smart, and Reliable ERP Workflow</div>
        </div>

        <div class="note">Manage your business smarter and faster.</div>
    </div>

</div>
</div>

<script>
function togglePassword(){
    const p=document.getElementById('password');
    p.type=p.type==='password'?'text':'password';
}
</script>

</body>
</html>