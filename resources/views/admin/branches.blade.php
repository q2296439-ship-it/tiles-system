@extends('layouts.admin')

@section('content')

<div class="content">

    <h2>🏬 Branch Management</h2>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div style="background: #d1fae5; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if($errors->any())
        <div style="background: #fee2e2; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
            <ul style="margin:0; padding-left:15px;">
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
                value="{{ old('name') }}"
                style="padding:10px; width:300px;" 
                required
            >

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
                @forelse($branches as $branch)
                <tr>
                    <td>{{ $branch->id }}</td>
                    <td>{{ $branch->name }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2">No branches found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection