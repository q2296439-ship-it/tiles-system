@extends('layouts.admin')

@section('content')

<style>
.container {
    max-width: 900px;
    margin: auto;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.grid {
    display: grid;
    grid-template-columns: repeat(2,1fr);
    gap: 15px;
}

input, select {
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    width: 100%;
}

button {
    padding: 14px;
    border-radius: 10px;
    border: none;
    background: #2563eb;
    color: white;
    font-weight: bold;
    margin-top: 15px;
}
</style>

<div class="container">
    @yield('form')
</div>

@endsection