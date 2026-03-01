@extends('layout.app')

@section('title', 'Pengaturan')

@push('css')
    <style>
        .settings-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .settings-card .card-header {
            background: #fff;
            border-bottom: 2px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .settings-card .card-body {
            padding: 1.5rem;
        }

        .badge-section {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.8rem;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .badge-wa {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: #fff;
        }

        .badge-schedule {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
        }

        .badge-token {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            color: #fff;
        }

        .form-label-custom {
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 10px 14px;
            font-size: 0.9rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .help-text {
            font-size: 0.8rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .btn-save {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            color: #fff;
            padding: 10px 28px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-test-wa {
            background: linear-gradient(135deg, #25d366, #128c7e);
            border: none;
            color: #fff;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }

        .btn-test-wa:hover {
            background: linear-gradient(135deg, #128c7e, #075e54);
            color: #fff;
        }

        .switch-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .form-check-input:checked {
            background-color: #25d366;
            border-color: #25d366;
        }

        .token-field {
            font-family: 'Courier New', monospace;
            font-size: 0.82rem;
            background: #f1f5f9;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
        }

        .status-inactive {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '36', 'title' => 'Pengaturan'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">Pengaturan</li>
        </ol>
    @endcomponent

    <form method="POST" action="{{ route('pengaturan.update') }}" id="settingsForm">
        @csrf

        <div class="row mt-2">
            {{-- LEFT COLUMN --}}
            <div class="col-lg-6 col-12">
                {{-- WhatsApp Recipients --}}
                <div class="card settings-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge-section badge-wa"><i class="bx bxl-whatsapp"></i> Penerima WhatsApp</span>
                        @if (!empty($settings->get('wa_recipients', '')))
                            <span class="status-indicator status-active"><i class="bx bxs-check-circle"></i> Aktif</span>
                        @else
                            <span class="status-indicator status-inactive"><i class="bx bxs-x-circle"></i> Belum
                                Diatur</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Nomor WhatsApp Penerima</label>
                            <textarea name="wa_recipients" class="form-control" rows="3" placeholder="08123456789, 08198765432">{{ $settings->get('wa_recipients', '') }}</textarea>
                            <div class="help-text">Pisahkan beberapa nomor dengan koma. Format: 08xxx atau 628xxx</div>
                        </div>

                        <div class="d-flex gap-2 align-items-center">
                            <input type="text" id="testPhone" class="form-control form-control-sm"
                                placeholder="Nomor untuk test kirim..." style="max-width:220px">
                            <button type="button" class="btn btn-test-wa btn-sm" onclick="testWA()">
                                <i class="bx bxl-whatsapp me-1"></i> Test Kirim
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Schedule --}}
                <div class="card settings-card">
                    <div class="card-header">
                        <span class="badge-section badge-schedule"><i class="bx bx-time-five"></i> Jadwal Kirim
                            Otomatis</span>
                    </div>
                    <div class="card-body">
                        <div class="switch-wrapper mb-3">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="waAutoSend" name="wa_auto_send"
                                    {{ $settings->get('wa_auto_send', '0') == '1' ? 'checked' : '' }}
                                    style="width:48px;height:24px">
                            </div>
                            <div>
                                <div class="fw-semibold text-dark" style="font-size:0.92rem">Kirim Otomatis Harian</div>
                                <div class="text-muted" style="font-size:0.8rem">Kirim laporan harian secara otomatis ke
                                    semua penerima</div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label-custom">Jam Kirim</label>
                            <input type="time" name="wa_schedule_time" class="form-control" style="max-width:180px"
                                value="{{ $settings->get('wa_schedule_time', '08:00') }}">
                            <div class="help-text">Laporan akan dikirim otomatis setiap hari pada jam ini (WIB)</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            <div class="col-lg-6 col-12">
                {{-- Qontak API Tokens --}}
                <div class="card settings-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="badge-section badge-token"><i class="bx bx-key"></i> Qontak API</span>
                        @if (!empty($settings->get('qontak_access_token', '')))
                            <span class="status-indicator status-active"><i class="bx bxs-check-circle"></i> Token
                                Tersedia</span>
                        @else
                            <span class="status-indicator status-inactive"><i class="bx bxs-x-circle"></i> Belum
                                Diatur</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label-custom">Access Token</label>
                            <input type="text" name="qontak_access_token" class="form-control token-field"
                                value="{{ $settings->get('qontak_access_token', '') }}"
                                placeholder="Bearer access token dari Qontak">
                        </div>

                        <div class="mb-3">
                            <label class="form-label-custom">Refresh Token</label>
                            <input type="text" name="qontak_refresh_token" class="form-control token-field"
                                value="{{ $settings->get('qontak_refresh_token', '') }}"
                                placeholder="Refresh token dari Qontak">
                        </div>

                        <div class="mb-0">
                            <label class="form-label-custom">Channel Integration ID</label>
                            <input type="text" name="qontak_channel_id" class="form-control token-field"
                                value="{{ $settings->get('qontak_channel_id', '') }}"
                                placeholder="ID channel WhatsApp di Qontak">
                            <div class="help-text">Dapatkan dari menu Integrations di dashboard Qontak</div>
                        </div>
                    </div>
                </div>

                {{-- Save --}}
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('pengaturan') }}" class="btn btn-outline-secondary px-4">
                        <i class="bx bx-reset me-1"></i> Reset
                    </a>
                    <button type="submit" class="btn btn-save">
                        <i class="bx bx-save me-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        function testWA() {
            var phone = document.getElementById('testPhone').value;
            if (!phone) {
                Swal.fire({
                    toast: true,
                    position: 'top',
                    icon: 'warning',
                    title: 'Masukkan nomor WhatsApp terlebih dahulu',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            Swal.fire({
                title: 'Kirim pesan test?',
                text: 'Pesan test akan dikirim ke ' + phone,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim',
                cancelButtonText: 'Batal'
            }).then(function(result) {
                if (result.isConfirmed) {
                    fetch('{{ route('pengaturan.test-wa') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                phone: phone
                            })
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: data.success ? 'success' : 'error',
                                title: data.message,
                                showConfirmButton: false,
                                timer: 4000
                            });
                        })
                        .catch(function(err) {
                            Swal.fire({
                                toast: true,
                                position: 'top',
                                icon: 'error',
                                title: 'Error: ' + err.message,
                                showConfirmButton: false,
                                timer: 4000
                            });
                        });
                }
            });
        }
    </script>
@endpush
