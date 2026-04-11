@extends('layouts.admin')

@section('content')

<style>
.container {
    max-width: 1000px;
    margin: auto;
}

.card {
    background: white;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

input {
    width: 100%;
    padding: 12px;
    border-radius: 10px;
    border: 1px solid #ddd;
    margin-bottom: 10px;
}

button {
    padding: 12px;
    width: 100%;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
}

button:hover {
    background: #1d4ed8;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #f9fafb;
    text-align: left;
}

.table th, .table td {
    padding: 12px;
    border-bottom: 1px solid #eee;
}
</style>

<div class="container">

    <h2 style="margin-bottom:20px;">🏬 Branch Management</h2>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div style="background:#d1fae5;padding:12px;margin-bottom:15px;border-radius:6px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR --}}
    @if($errors->any())
        <div style="background:#fee2e2;padding:12px;margin-bottom:15px;border-radius:6px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ADD FORM --}}
    <div class="card">

        <h3>Add Branch</h3>

        <form method="POST" action="/admin/branches/store">
            @csrf

            <input 
                type="text" 
                name="name" 
                placeholder="Branch Name"
                required
            >

            <input 
                type="text" 
                name="address" 
                placeholder="Branch Address"
                required
            >

            <button type="submit">➕ Add Branch</button>
        </form>

    </div>

    {{-- LIST --}}
    <div class="card">

        <h3>Branch List</h3>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Branch Name</th>
                    <th>Address</th>
                </tr>
            </thead>

            <tbody>
                @forelse($branches as $branch)
                <tr>
                    <td>{{ $branch->id }}</td>
                    <td>{{ $branch->name }}</td>
                    <td>{{ $branch->address ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3">No branches found</td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>

</div>

@endsection