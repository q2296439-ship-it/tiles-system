@extends($layout)

@section('content')

<style>
.wrap{max-width:1100px;margin:auto;}
.card{
background:#dbeafe;
border:2px solid #93c5fd;
border-radius:14px;
padding:25px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.title{
font-size:28px;
font-weight:900;
text-align:center;
color:#0f172a;
margin-bottom:5px;
}
.sub{
text-align:center;
font-size:12px;
color:#475569;
margin-bottom:20px;
}
.form-grid{
display:grid;
grid-template-columns:1fr;
gap:12px;
margin-bottom:20px;
}
.input,.textarea{
width:100%;
padding:12px;
border:1px solid #cbd5e1;
border-radius:10px;
font-size:14px;
}
.textarea{min-height:120px;resize:vertical;}
.btn{
background:#2563eb;
color:#fff;
border:none;
padding:12px 18px;
border-radius:10px;
font-weight:700;
cursor:pointer;
}
.item{
background:#fff;
border:1px solid #cbd5e1;
border-radius:12px;
padding:15px;
margin-bottom:12px;
}
.head{
font-weight:800;
font-size:18px;
color:#0f172a;
}
.meta{
font-size:12px;
color:#64748b;
margin-top:4px;
margin-bottom:8px;
}
.del{
background:#dc2626;
color:#fff;
border:none;
padding:8px 12px;
border-radius:8px;
cursor:pointer;
margin-top:10px;
}
.empty{
text-align:center;
padding:20px;
color:#64748b;
}
</style>

@php
$role = strtolower(auth()->user()->role ?? '');
$isAdmin = $role === 'admin';
@endphp

<div class="wrap">
<div class="card">

<div class="title">📢 ANNOUNCEMENTS</div>
<div class="sub">Admin / Manager Broadcast Center</div>

@if(session('success'))
<div style="background:#dcfce7;padding:10px;border-radius:10px;margin-bottom:15px;">
    {{ session('success') }}
</div>
@endif

{{-- FORM ONLY FOR ADMIN MAIN PAGE --}}
@if($isAdmin && !request()->has('view'))
<form method="POST" action="{{ route('announcements.store') }}">
@csrf

<div class="form-grid">
    <input type="text" name="title" class="input" placeholder="Announcement Title" required>

    <textarea name="message" class="textarea" placeholder="Write message here..." required></textarea>

    <button type="submit" class="btn">📢 Post Announcement</button>
</div>
</form>

<hr style="margin:20px 0;">
@endif


@forelse($announcements as $row)
<div class="item">

    <div class="head">{{ $row->title }}</div>

    <div class="meta">
        Posted {{ $row->created_at->format('M d, Y h:i A') }}
    </div>

    <div>{{ $row->message }}</div>

    {{-- REMOVE BUTTON ONLY SA ADMIN MAIN PAGE --}}
    @if($isAdmin && !request()->has('view'))
    <form method="POST" action="{{ route('announcements.delete',$row->id) }}">
        @csrf
        @method('DELETE')
        <button class="del">🗑 Remove</button>
    </form>
    @endif

</div>
@empty
<div class="empty">No announcements yet.</div>
@endforelse

</div>
</div>

@endsection