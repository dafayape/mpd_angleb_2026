@extends('layout.app')

@section('title', 'Daily Report')

@section('content')
    <div class="container-fluid">

        <div class="row no-print mb-3">
            <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h4 class="mb-0 font-size-18">Daily Report (Text Format)</h4>
                <div class="d-flex flex-column flex-md-row gap-2">
                    <form class="d-flex align-items-center gap-2" method="GET">
                        <input type="date" name="start_date" class="form-control" value="{{ $start_date }}">
                        <span class="text-muted fw-bold">&mdash;</span>
                        <input type="date" name="end_date" class="form-control" value="{{ $end_date }}">
                        <button type="submit" class="btn btn-primary d-none d-md-block">Terapkan</button>
                        <button type="submit" class="btn btn-primary d-block d-md-none"><i
                                class="mdi mdi-magnify"></i></button>
                    </form>
                    <button onclick="copyReportText()" class="btn btn-success text-nowrap"><i class="bx bx-copy me-1"></i>
                        Copy Text</button>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-body p-4" id="report-content">
                        <div class="content-text"
                            style="font-family: Arial, sans-serif; font-size: 15px; line-height: 1.8; color: #333;">
                            <p>Yth. <strong>Bapak Kepala Badan Kebijakan Transportasi</strong></p>

                            <p class="text-justify">
                                Dengan hormat, izin melaporkan perkembangan pemantauan pergerakan orang pada periode
                                Angkutan Lebaran 2026 dengan menggunakan <em>Mobile Positioning Data</em> (MPD) posisi dari
                                <strong>{{ $period_string }}</strong> sebagai berikut:
                            </p>

                            <p class="mb-1">A.&nbsp;&nbsp;&nbsp;Pergerakan NASIONAL:</p>
                            <ol class="ps-4 mb-3">
                                <li class="mb-1">Total/akumulasi realisasi pergerakan orang adalah sebanyak
                                    <strong>{{ number_format($nasional_total, 0, ',', '.') }}</strong> orang;</li>
                                <li class="mb-1">Realisasi pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari
                                    <strong>{{ $nasional_highest_date }}</strong> sebanyak
                                    <strong>{{ number_format($nasional_highest_total, 0, ',', '.') }}</strong> orang.</li>
                            </ol>

                            <p class="mb-1">B.&nbsp;&nbsp;&nbsp;Pergerakan JABODETABEK:</p>
                            <ol class="ps-4 mb-3">
                                <li class="mb-1">Total/akumulasi realisasi pergerakan orang adalah sebanyak
                                    <strong>{{ number_format($jabo_total, 0, ',', '.') }}</strong> orang;</li>
                                <li class="mb-1">Realisasi pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari
                                    <strong>{{ $jabo_highest_date }}</strong> sebanyak
                                    <strong>{{ number_format($jabo_highest_total, 0, ',', '.') }}</strong> orang.</li>
                            </ol>

                            <p class="mb-3">
                                Demikian disampaikan dan mohon arahannya.
                            </p>

                            <p class="mb-0">
                                Terima kasih.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function copyReportText() {
                const text = `Yth. *Bapak Kepala Badan Kebijakan Transportasi*

Dengan hormat, izin melaporkan perkembangan pemantauan pergerakan orang pada periode Angkutan Lebaran 2026 dengan menggunakan _Mobile Positioning Data_ (MPD) posisi dari *{{ $period_string }}* sebagai berikut:
A.\tPergerakan NASIONAL:
1. Total/akumulasi realisasi pergerakan orang adalah sebanyak *{{ number_format($nasional_total, 0, ',', '.') }}* orang;
2. Realisasi pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{{ $nasional_highest_date }}* sebanyak *{{ number_format($nasional_highest_total, 0, ',', '.') }}* orang.

B.\tPergerakan JABODETABEK:
1. Total/akumulasi realisasi pergerakan orang adalah sebanyak *{{ number_format($jabo_total, 0, ',', '.') }}* orang;
2. Realisasi pergerakan orang arus keberangkatan TERTINGGI terjadi pada hari *{{ $jabo_highest_date }}* sebanyak *{{ number_format($jabo_highest_total, 0, ',', '.') }}* orang.

Demikian disampaikan dan mohon arahannya.

Terima kasih.`;

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(text).then(() => {
                        const btn = document.querySelector('button.btn-success');
                        const originalHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="bx bx-check-double me-1"></i> Tersalin!';
                        setTimeout(() => {
                            btn.innerHTML = originalHtml;
                        }, 2000);
                    }).catch(err => {
                        alert('Gagal menyalin teks: ' + err);
                    });
                } else {
                    // Fallback
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    document.body.appendChild(textarea);
                    textarea.select();
                    try {
                        document.execCommand('copy');
                        const btn = document.querySelector('button.btn-success');
                        const originalHtml = btn.innerHTML;
                        btn.innerHTML = '<i class="bx bx-check-double me-1"></i> Tersalin!';
                        setTimeout(() => {
                            btn.innerHTML = originalHtml;
                        }, 2000);
                    } catch (err) {
                        alert('Gagal menyalin teks!');
                    }
                    document.body.removeChild(textarea);
                }
            }
        </script>


    </div>
@endsection
