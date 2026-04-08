@extends('layouts.admin')

@section('content')

<div class="content">

    <h2 style="margin-bottom:20px;">🏬 Branch Management</h2>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div style="background:#d1fae5;padding:12px;margin-bottom:20px;border-radius:6px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR MESSAGE --}}
    @if($errors->any())
        <div style="background:#fee2e2;padding:12px;margin-bottom:20px;border-radius:6px;">
            <ul style="margin:0; padding-left:15px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ADD FORM --}}
    <div class="card" style="margin-bottom:25px;">
        <h3 style="margin-bottom:15px;">Add Branch</h3>

        <form method="POST" action="/admin/branches/store" style="display:flex; gap:10px;">
            @csrf

            <input 
                type="text" 
                name="name" 
                placeholder="Enter branch name"
                value="{{ old('name') }}"
                style="padding:10px; width:300px; border:1px solid #ccc; border-radius:5px;" 
                required
            >

            <button type="submit"
                style="padding:10px 20px; background:#2563eb; color:white; border:none; border-radius:5px; cursor:pointer;">
                Add
            </button>
        </form>
    </div>

    {{-- LIST --}}
    <div class="card">
        <h3 style="margin-bottom:15px;">Branch List</h3>

        <table>
            <thead>
                <tr>
                    <th style="width:60px;">ID</th>
                    <th>Branch Name</th>
                    <th style="width:180px;">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($branches as $branch)
                <tr>

                    {{-- ID --}}
                    <td>{{ $branch->id }}</td>

                    {{-- EDIT FORM --}}
                    <td>
                        <form method="POST" action="/admin/branches/update/{{ $branch->id }}" style="display:flex; gap:10px;">
                            @csrf

                            <input 
                                type="text" 
                                name="name"
                                value="{{ $branch->name }}"
                                style="padding:8px; width:100%; border:1px solid #ccc; border-radius:5px;"
                                required
                    </td>

                    {{-- ACTION --}}
                    <td style="display:flex; gap:5px;">

                            <button type="submit"
                                style="background:#16a34a;color:white;border:none;padding:6px 12px;border-radius:5px;cursor:pointer;">
                                Save
                            </button>
                        </form>

                        <form method="POST" action="/admin/branches/delete/{{ $branch->id }}"
                              onsubmit="return confirm('Delete this branch?')">
                            @csrf

                            <button type="submit"
                                style="background:#dc2626;color:white;border:none;padding:6px 12px;border-radius:5px;cursor:pointer;">
                                Delete
                            </button>
                        </form>

                    </td>

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