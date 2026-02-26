@extends('layout.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    @component('layout.partials.page-header', ['number' => '33', 'title' => 'Manajemen Akun Pengguna'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pengguna</li>
        </ol>
    @endcomponent

    @include('users.alert')

    <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">+ Tambah Pengguna</a>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-nowrap table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th style="width: 70px;"></th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $key => $user)
                            <tr>
                                <td>{{ $users->firstItem() + $key }}</td>
                                <td>
                                    <div class="avatar-xs">
                                        @if ($user->photo)
                                            <img class="rounded-circle avatar-xs"
                                                src="{{ asset('storage/photos/' . $user->photo) }}" alt="">
                                        @else
                                            <span
                                                class="avatar-title rounded-circle">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ ucfirst($user->role) }}</td>
                                <td>
                                    <a href="{{ route('users.show', $user->id) }}" class="btn btn-info btn-sm">Lihat</a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Yakin hapus pengguna ini?')"
                                            class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
@endsection
