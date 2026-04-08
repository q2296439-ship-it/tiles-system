@extends('layouts.app') {{-- or kung ano layout mo --}}

@section('content')

<div class="content">

    <h2>🏬 Branch Management</h2>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div style="background: #d1fae5; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- ADD FORM --}}
    <div class="card">
        <h3>Add Branch</h3>

        <form method="POST" action="/admin/branches/store">
            @csrf

            <input type="text" name="name" placeholder="Branch Name"
                style="padding:10px; width:300px;" required>

            <button type="submit" style="padding:10px;">Add</button>
        </form>
    </div>

    {{-- LIST --}}
    <div class="card">
        <h3>Branch List</h3>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Branch Name</th>
                </tr>
            </thead>

            <tbody>
                @foreach($branches as $branch)
                <tr>
                    <td>{{ $branch->id }}</td>
                    <td>{{ $branch->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

@endsection