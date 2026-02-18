@extends('layout.app')

@section('title', 'Profil Pengguna')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Profil Pengguna</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Edit Profil</h4>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="text-center mb-4">
                            <div class="position-relative d-inline-block">
                                @if($user->photo && \Illuminate\Support\Facades\Storage::exists('public/photos/' . $user->photo))
                                    <img src="{{ asset('storage/photos/' . $user->photo) }}" alt="Profile Photo" 
                                        class="rounded-circle avatar-xl img-thumbnail object-fit-cover" style="width: 120px; height: 120px;">
                                @else
                                    <div class="avatar-xl bg-soft-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px;">
                                        <span class="text-primary fw-bold font-size-24">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    </div>
                                @endif
                                <div class="position-absolute bottom-0 end-0">
                                    <label for="photo" class="btn btn-primary btn-sm rounded-circle shadow-sm" style="width: 32px; height: 32px; padding: 0; line-height: 30px;" data-bs-toggle="tooltip" title="Ubah Foto">
                                        <i class="bx bx-camera font-size-16"></i>
                                    </label>
                                    <input type="file" id="photo" name="photo" class="d-none" accept="image/jpeg,image/png,image/jpg" onchange="previewImage(this)">
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted d-block">Klik ikon kamera untuk mengubah foto</small>
                                <small class="text-muted font-size-11">Format: JPG, JPEG, PNG. Maks: 2MB</small>
                                @error('photo')
                                    <div class="text-danger font-size-12 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Email ini digunakan untuk login.</div>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly disabled>
                            <div class="form-text">Role tidak dapat diubah sendiri. Hubungi admin untuk perubahan hak akses.</div>
                        </div>

                        <hr class="my-4">
                        <h5 class="font-size-14 mb-3 text-muted"><i class="bx bx-lock-alt me-1"></i> Ganti Password</h5>
                        <div class="alert alert-info py-2 font-size-13 mb-3">
                            <i class="bx bx-info-circle me-1"></i> Biarkan kosong jika tidak ingin mengubah password.
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                id="password" name="password" autocomplete="new-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" 
                                id="password_confirmation" name="password_confirmation" autocomplete="new-password">
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-light">Batal</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var img = input.closest('.position-relative').querySelector('img');
                var placeholder = input.closest('.position-relative').querySelector('.avatar-xl');
                
                if (img) {
                    img.src = e.target.result;
                } else if (placeholder) {
                    // Replace placeholder with new image
                    var newImg = document.createElement('img');
                    newImg.src = e.target.result;
                    newImg.alt = 'Profile Photo';
                    newImg.className = 'rounded-circle avatar-xl img-thumbnail object-fit-cover';
                    newImg.style.width = '120px';
                    newImg.style.height = '120px';
                    placeholder.parentNode.replaceChild(newImg, placeholder);
                }
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endpush
