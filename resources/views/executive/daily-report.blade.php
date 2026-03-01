@extends('layout.app')

@section('title', 'Daily Report')

@push('css')
    <style>
        .filter-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .report-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }

        .report-preview {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem 2rem;
            font-family: 'Poppins', sans-serif;
            font-size: 0.92rem;
            line-height: 1.8;
            color: #1e293b;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .report-preview .bold {
            font-weight: 700;
            color: #0f172a;
        }

        .report-preview .italic {
            font-style: italic;
            color: #475569;
        }

        .btn-copy {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border: none;
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-copy:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-copy.copied {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .btn-wa {
            background: linear-gradient(135deg, #25d366, #128c7e);
            border: none;
            color: #fff;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .btn-wa:hover {
            background: linear-gradient(135deg, #128c7e, #075e54);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eff6ff;
            color: #1e40af;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.82rem;
        }

        .filter-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #475569;
            margin-bottom: 4px;
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '23', 'title' => 'Daily Report'])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Executive Summary</a></li>
            <li class="breadcrumb-item active">Daily Report</li>
        </ol>
    @endcomponent

    {{-- Filter Bar --}}
    <div class="row mb-3 mt-2">
        <div class="col-12">
            <div class="card filter-card">
                <div class="card-body py-3">
                    <form id="filterForm" method="GET" action="{{ route('executive.daily-report') }}"
                        class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-3">
                            <label class="filter-label">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $start_date }}"
                                min="2026-03-13" max="2026-03-30">
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="filter-label">Tanggal Akhir</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $end_date }}"
                                min="2026-03-13" max="2026-03-30">
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="filter-label">Tipe Data</label>
                            <select name="kategori" class="form-select">
                                <option value="REAL" {{ ($kategori ?? 'REAL') == 'REAL' ? 'selected' : '' }}>Real</option>
                                <option value="FORECAST" {{ ($kategori ?? '') == 'FORECAST' ? 'selected' : '' }}>Forecast
                                </option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="filter-label">Operator Seluler</label>
                            <select name="opsel" class="form-select">
                                <option value="ALL" {{ ($opsel ?? 'ALL') == 'ALL' ? 'selected' : '' }}>Semua Opsel
                                </option>
                                <option value="XL" {{ ($opsel ?? '') == 'XL' ? 'selected' : '' }}>XL Axiata</option>
                                <option value="TSEL" {{ ($opsel ?? '') == 'TSEL' ? 'selected' : '' }}>Telkomsel</option>
                                <option value="IOH" {{ ($opsel ?? '') == 'IOH' ? 'selected' : '' }}>Indosat (IOH)
                                </option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="filter-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-filter-alt me-1"></i> Terapkan
                            </button>
                        </div>
                        <div class="col-lg-2 col-md-3">
                            <label class="filter-label">&nbsp;</label>
                            <a href="{{ route('executive.daily-report') }}" class="btn btn-outline-secondary w-100">
                                <i class="bx bx-reset me-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Pills --}}
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap gap-2">
            <span class="stat-pill"><i class="bx bx-globe"></i> Nasional:
                {{ number_format($nasional_total, 0, ',', '.') }} pergerakan</span>
            <span class="stat-pill"><i class="bx bx-buildings"></i> Jabodetabek:
                {{ number_format($jabo_total, 0, ',', '.') }} pergerakan</span>
            <span class="stat-pill" style="background:#fef3c7;color:#92400e"><i class="bx bx-calendar"></i>
                Periode: {{ $period_string }}</span>
        </div>
    </div>

    {{-- Report Preview --}}
    <div class="row">
        <div class="col-12">
            <div class="card report-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3"
                    style="border-bottom:2px solid #e2e8f0">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark"><i class="bx bx-file-blank text-primary me-2"></i>Preview
                            Laporan Harian</h5>
                    </div>
                    <div class="d-flex gap-2">
                        <button id="btnCopy" class="btn btn-copy" onclick="copyReport()">
                            <i class="bx bx-copy me-1"></i> Salin Teks
                        </button>
                        <button id="btnWa" class="btn btn-wa" onclick="sendWhatsApp()">
                            <i class="bx bxl-whatsapp me-1"></i> Kirim WA
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="report-preview" id="reportPreview">Yth. <span class="bold">Bapak Kepala Badan Kebijakan
                            Transportasi</span>

                        Dengan hormat, izin melaporkan perkembangan pemantauan pergerakan orang pada periode Angleb 2026
                        dengan menggunakan <span class="italic">Mobile Positioning Data</span> (MPD) posisi dari <span
                            class="bold">{{ $period_string }}</span> sebagai berikut:

                        A. Pergerakan NASIONAL:
                        1. Total/akumulasi {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'prediksi' : 'realisasi' }} pergerakan
                        orang adalah sebanyak <span class="bold">{{ number_format($nasional_total, 0, ',', '.') }}</span>
                        orang;
                        2. {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'Prediksi' : 'Realisasi' }} pergerakan orang arus
                        keberangkatan TERTINGGI terjadi pada hari <span class="bold">{{ $nasional_highest_date }}</span>
                        sebanyak <span class="bold">{{ number_format($nasional_highest_total, 0, ',', '.') }}</span>
                        orang.

                        B. Pergerakan JABODETABEK:
                        1. Total/akumulasi {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'prediksi' : 'realisasi' }} pergerakan
                        orang adalah sebanyak <span class="bold">{{ number_format($jabo_total, 0, ',', '.') }}</span>
                        orang;
                        2. {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'Prediksi' : 'Realisasi' }} pergerakan orang arus
                        keberangkatan TERTINGGI terjadi pada hari <span class="bold">{{ $jabo_highest_date }}</span>
                        sebanyak <span class="bold">{{ number_format($jabo_highest_total, 0, ',', '.') }}</span> orang.

                        Demikian disampaikan dan mohon arahannya.

                        Terima kasih.</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden plain text for copy --}}
    <textarea id="plainTextReport" style="position:absolute;left:-9999px" readonly>Yth. *Bapak Kepala Badan Kebijakan Transportasi*

Dengan hormat, izin melaporkan perkembangan pemantauan pergerakan orang pada periode Angleb 2026 dengan menggunakan _Mobile Positioning Data_ (MPD) posisi dari *{{ $period_string }}* sebagai berikut:

A.	Pergerakan NASIONAL:
1. Total/akumulasi {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'prediksi' : 'realisasi' }} pergerakan orang adalah sebanyak *{{ number_format($nasional_total, 0, ',', '.') }}* orang;
2. {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'Prediksi' : 'Realisasi' }} pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{{ $nasional_highest_date }}* sebanyak *{{ number_format($nasional_highest_total, 0, ',', '.') }}* orang.

B.	Pergerakan JABODETABEK:
1. Total/akumulasi {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'prediksi' : 'realisasi' }} pergerakan orang adalah sebanyak *{{ number_format($jabo_total, 0, ',', '.') }}* orang;
2. {{ ($kategori ?? 'REAL') == 'FORECAST' ? 'Prediksi' : 'Realisasi' }} pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{{ $jabo_highest_date }}* sebanyak *{{ number_format($jabo_highest_total, 0, ',', '.') }}* orang.

Demikian disampaikan dan mohon arahannya.

Terima kasih.</textarea>
@endsection

@push('scripts')
    <script>
        function copyReport() {
            var text = document.getElementById('plainTextReport').value;
            navigator.clipboard.writeText(text).then(function() {
                var btn = document.getElementById('btnCopy');
                btn.classList.add('copied');
                btn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
                setTimeout(function() {
                    btn.classList.remove('copied');
                    btn.innerHTML = '<i class="bx bx-copy me-1"></i> Salin Teks';
                }, 2500);
            }).catch(function() {
                // fallback
                var ta = document.getElementById('plainTextReport');
                ta.style.position = 'fixed';
                ta.style.left = '0';
                ta.style.top = '0';
                ta.style.opacity = '0.01';
                ta.select();
                document.execCommand('copy');
                ta.style.position = 'absolute';
                ta.style.left = '-9999px';
                ta.style.opacity = '1';
                var btn = document.getElementById('btnCopy');
                btn.classList.add('copied');
                btn.innerHTML = '<i class="bx bx-check me-1"></i> Tersalin!';
                setTimeout(function() {
                    btn.classList.remove('copied');
                    btn.innerHTML = '<i class="bx bx-copy me-1"></i> Salin Teks';
                }, 2500);
            });
        }

        function sendWhatsApp() {
            var btn = document.getElementById('btnWa');
            btn.disabled = true;
            btn.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Mengirim...';

            fetch('{{ route('executive.daily-report.send-wa') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'send_whatsapp',
                        start_date: '{{ $start_date }}',
                        end_date: '{{ $end_date }}',
                        kategori: '{{ $kategori ?? 'REAL' }}',
                        opsel: '{{ $opsel ?? 'ALL' }}'
                    })
                })
                .then(function(res) {
                    return res.json();
                })
                .then(function(data) {
                    btn.disabled = false;
                    if (data.success) {
                        btn.innerHTML = '<i class="bx bx-check me-1"></i> Terkirim!';
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'success',
                            title: data.message || 'Pesan WhatsApp terkirim!',
                            showConfirmButton: false,
                            timer: 3000
                        });
                    } else {
                        btn.innerHTML = '<i class="bx bxl-whatsapp me-1"></i> Kirim WA';
                        Swal.fire({
                            toast: true,
                            position: 'top',
                            icon: 'error',
                            title: data.message || 'Gagal mengirim',
                            showConfirmButton: false,
                            timer: 4000
                        });
                    }
                    setTimeout(function() {
                        btn.innerHTML = '<i class="bx bxl-whatsapp me-1"></i> Kirim WA';
                    }, 3000);
                })
                .catch(function(err) {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bx bxl-whatsapp me-1"></i> Kirim WA';
                    Swal.fire({
                        toast: true,
                        position: 'top',
                        icon: 'error',
                        title: 'Gagal mengirim: ' + err.message,
                        showConfirmButton: false,
                        timer: 4000
                    });
                });
        }
    </script>
@endpush
