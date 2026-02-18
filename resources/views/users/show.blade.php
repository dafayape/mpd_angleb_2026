@extends('layout.app')

@section('title', 'Detail Pengguna')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Detail Pengguna</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Pengguna</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">Informasi Pengguna</h5>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 200px;">Nama</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th>Foto</th>
                    <td>
                        <div class="avatar-md">
                            @if ($user->photo)
                                <img src="{{ asset('storage/photos/' . $user->photo) }}" alt="" class="img-thumbnail">
                            @else
                                <img src="{{ asset('assets/images/users/avatar-1.jpg') }}" alt="" class="img-thumbnail">
                            @endif
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>{{ ucfirst($user->role) }}</td>
                </tr>
                <tr>
                    <th>Dibuat</th>
                    <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                </tr>
            </table>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
@endsection
