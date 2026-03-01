@extends('layout.app')

@section('title', $title)

@section('content')
    @component('layout.partials.page-header', ['number' => '10', 'title' => $title])
        <ol class="breadcrumb m-0 mb-0">
            @foreach ($breadcrumb as $crumb)
                @if ($loop->last)
                    <li class="breadcrumb-item active">{{ $crumb }}</li>
                @else
                    <li class="breadcrumb-item"><a href="#">{{ $crumb }}</a></li>
                @endif
            @endforeach
        </ol>
    @endcomponent

    @push('css')
        <style>
            .table th,
            .table td {
                vertical-align: middle;
                font-size: 11.5px;
            }

            .table-custom th {
                background-color: #c8d9e8 !important;
                font-weight: 600;
                font-size: 12px;
                border-color: #999;
            }

            .table-custom td {
                font-size: 12.5px;
                border-color: #ccc;
            }

            .section-badge {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 30px;
                height: 30px;
                background-color: #007bff;
                color: white;
                border-radius: 50%;
                font-weight: bold;
                font-size: 0.9rem;
                margin-right: 1rem;
                flex-shrink: 0;
            }

            .content-card {
                border: none;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                margin-bottom: 2rem;
            }

            .border-custom-thick {
                border-width: 2px !important;
                border-color: #aab5c3 !important;
            }
        </style>
    @endpush

    <!-- Section 01 -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">01</span>
                    <h5 class="fw-bold text-navy mb-0">20 Besar Kabupaten/Kota Asal Nasional berdasarkan kekuatan Netflow
                        MPD</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">
                    <div class="row">
                        <!-- Text Left -->
                        <div class="col-lg-4 mb-4">
                            <p style="text-align: justify; font-size: 13px;">
                                Untuk mengatasi anomali pola Origin - Destination (O-D) pada data MPD di level
                                kabupaten/kota, analisis ini menggunakan pendekatan net flow, yaitu <strong>selisih antara
                                    jumlah pergerakan masuk (inflow) dan keluar (outflow)</strong> pada masing-masing
                                wilayah.
                            </p>
                            <p style="text-align: justify; font-size: 13px;">
                                Perhitungan <i>netflow</i> bertujuan untuk: Mengidentifikasi wilayah yang berperan sebagai
                                penarik (destination/tujuan) atau wilayah pelepas (origin/asal) pergerakan orang berdasarkan
                                selisih total pergerakan masuk dan keluar pada masing-masing kabupaten/kota.
                            </p>

                            <div class="p-3 mb-3" style="background-color: #e2e8f0; border-radius: 8px;">
                                <h6 class="fw-bold mb-1">Asumsi :</h6>
                                <p class="mb-0" style="font-size: 13px;">Pergerakan masuk &approx; tujuan<br>Pergerakan
                                    keluar &approx; asal</p>
                            </div>

                            <div class="p-3 mb-3" style="background-color: #e2e8f0; border-radius: 8px;">
                                <h6 class="fw-bold mb-1">Konsep dasar Netflow :</h6>
                                <p class="mb-0" style="font-size: 13px;">Netflow = Total Tujuan (Inflow) - Total Asal
                                    (Outflow)</p>
                            </div>

                            <div class="p-3 mb-3" style="background-color: #e2e8f0; border-radius: 8px;">
                                <h6 class="fw-bold mb-1">Interpretasi:</h6>
                                <p class="mb-0" style="font-size: 13px; line-height:1.6;">
                                    <strong>Netflow (+)</strong> &rarr; wilayah yang dominan berperan sebagai <strong>tujuan
                                        pergerakan</strong><br>
                                    <strong>Netflow (-)</strong> &rarr; wilayah yang dominan berperan sebagai <strong>asal
                                        pergerakan</strong>
                                </p>
                            </div>

                            <p style="text-align: justify; font-size: 13px;">
                                Adapun dari hasil perhitungan <i>netflow</i> yang telah dilakukan, TOP 20 Kabupaten/Kota
                                untuk Origin dan Destination dari pergerakan hasil MPD dapat dilihat pada tabel:
                            </p>
                        </div>

                        <!-- Table Right -->
                        <div class="col-lg-8">
                            <div class="table-responsive border rounded border-custom-thick">
                                <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">Rank</th>
                                            <th class="text-start">Kabupaten/Kota</th>
                                            <th class="text-end">Pergerakan Keluar<br>(Outflow)</th>
                                            <th class="text-end">Pergerakan Masuk<br>(Inflow)</th>
                                            <th class="text-end">Netflow</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($top_origin_netflow as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-start">{{ $row['name'] }}</td>
                                                <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">
                                                    @if ($row['netflow'] < 0)
                                                        - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                                    @else
                                                        {{ number_format($row['netflow'], 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-3 text-muted">Belum ada data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Alert below table -->
                            <div class="mt-3 p-3" style="background-color: #dbeafe; border-radius: 8px;">
                                <p class="mb-0" style="font-size: 13px;">
                                    <strong>Peringkat Kabupaten/kota asal</strong> ditentukan berdasarkan <strong>besarnya
                                        nilai net flow</strong>, yang menunjukkan kekuatan fungsi wilayah sebagai asal
                                    pergerakan. Sedangkan <strong>total pergerakan menunjukkan skala volume
                                        pergerakan</strong>
                                </p>
                            </div>
                            <p class="mt-2 text-muted" style="font-size: 11px; font-style: italic;">
                                Catatan : Angka selisih (net flow) sebaiknya tidak ditambahkan ke angka pergerakan kota
                                tujuan maupun asal, karena net flow diposisikan sebagai indikator, bukan sebagai komponen
                                volume pergerakan
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 02 -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">02</span>
                    <h5 class="fw-bold text-navy mb-0">20 Besar Kabupaten/Kota Tujuan Nasional berdasarkan kekuatan Netflow
                        MPD</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">
                    <div class="row">
                        <!-- Table Left -->
                        <div class="col-lg-8 mb-4">
                            <div class="table-responsive border rounded border-custom-thick">
                                <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th style="width: 40px;">Rank</th>
                                            <th class="text-start">Kabupaten/Kota</th>
                                            <th class="text-end">Pergerakan Keluar<br>(Outflow)</th>
                                            <th class="text-end">Pergerakan Masuk<br>(Inflow)</th>
                                            <th class="text-end">Netflow</th>
                                            <th>Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($top_dest_netflow as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-start">{{ $row['name'] }}</td>
                                                <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">
                                                    @if ($row['netflow'] < 0)
                                                        - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                                    @else
                                                        {{ number_format($row['netflow'], 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-3 text-muted">Belum ada data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Alert below table -->
                            <div class="mt-3 p-3" style="background-color: #dbeafe; border-radius: 8px;">
                                <p class="mb-0" style="font-size: 13px;">
                                    <strong>Peringkat Kabupaten/kota tujuan</strong> ditentukan berdasarkan <strong>besarnya
                                        nilai net flow</strong>, yang menunjukkan kekuatan fungsi wilayah sebagai tujuan
                                    pergerakan. Sedangkan <strong>total pergerakan menunjukkan skala volume
                                        pergerakan</strong>, dan tidak digunakan sebagai dasar penentuan peringkat.
                                </p>
                            </div>
                            <div class="mt-3 p-3"
                                style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <p class="mb-0" style="font-size: 13px;">
                                    <strong>Kabupaten Bogor</strong> memiliki total pergerakan masuk yang lebih besar, namun
                                    nilai netflow yang lebih rendah dibandingkan dengan kota lain, hal ini menunjukkan bahwa
                                    <strong>arus masuk dan keluar di wilayah tersebut cenderung lebih seimbang.</strong>
                                </p>
                            </div>
                        </div>

                        <!-- Text Right -->
                        <div class="col-lg-4">
                            <p style="text-align: justify; font-size: 13px;">
                                Sebagai validasi tambahan terhadap fungsi wilayah, diperlukan analisis <strong>Net Flow
                                    Ratio (NFR)</strong>, yaitu <strong>perbandingan antara selisih pergerakan masuk dan
                                    keluar terhadap total pergerakan</strong>. Namun hasil tersebut digunakan sebagai
                                <strong>indikator pendukung</strong>, bukan pengganti net flow absolut.
                            </p>

                            <div class="p-3 mb-3 text-center" style="background-color: #e2e8f0; border-radius: 8px;">
                                <h5 class="fw-bold mb-0" style="font-size: 15px;">NFR = <span
                                        style="font-style: italic;">(Inflow - Outflow) / (Inflow + Outflow)</span></h5>
                            </div>

                            <h6 class="fw-bold mb-1">Interpretasi:</h6>
                            <p style="font-size: 13px; line-height: 1.8;">
                                Mendekati +1 &rarr; Tujuan sangat dominan<br>
                                Mendekati 0 &rarr; Seimbang/transit<br>
                                Mendekati -1 &rarr; Asal sangat dominan
                            </p>

                            <p style="font-size: 13px; margin-top: 1rem;">
                                Sehingga, terdapat <strong>3 indikator</strong> dalam analisis Net Flow, dengan peran
                                berbeda:
                            </p>

                            <table class="table table-bordered table-sm text-center" style="font-size: 11.5px;">
                                <thead style="background-color: #c8d9e8;">
                                    <tr>
                                        <th>Indikator</th>
                                        <th>Peran</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-start fw-semibold">Net Flow Absolut</td>
                                        <td class="text-start">Penentu peringkat utama</td>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-semibold">Total Inflow</td>
                                        <td class="text-start">Skala pergerakan</td>
                                    </tr>
                                    <tr>
                                        <td class="text-start fw-semibold">Net Flow Ratio (NFR)</td>
                                        <td class="text-start">Validasi fungsi wilayah</td>
                                    </tr>
                                </tbody>
                            </table>

                            <p class="mt-2 text-muted" style="font-size: 11px; font-style: italic;">
                                Disclaimer :<br>
                                NFR tidak dipakai untuk penentuan ranking utama
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 03 -->
    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header d-flex align-items-center bg-white"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <span class="section-badge">03</span>
                    <h5 class="fw-bold text-navy mb-0">Perbandingan 20 Besar Asal dan Tujuan Berdasarkan Kekuatan NFR</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2.5rem 1.5rem;">
                    <div class="row">
                        <!-- Left Table -->
                        <div class="col-lg-6 mb-4 mb-lg-0">
                            <h6 class="fw-bold text-center mb-3">20 Besar Kabupaten/Kota Asal Nasional berdasarkan kekuatan
                                NFR</h6>
                            <div class="table-responsive border rounded border-custom-thick">
                                <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;">Rank</th>
                                            <th class="text-start">Kabupaten/Kota</th>
                                            <th class="text-end" style="font-size: 10px;">Pergerakan Keluar<br>(Outflow)
                                            </th>
                                            <th class="text-end" style="font-size: 10px;">Pergerakan Masuk<br>(Inflow)
                                            </th>
                                            <th class="text-end">Netflow</th>
                                            <th>NFR</th>
                                            <th>Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($top_origin_nfr as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-start">{{ $row['name'] }}</td>
                                                <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">
                                                    @if ($row['netflow'] < 0)
                                                        - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                                    @else
                                                        {{ number_format($row['netflow'], 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($row['nfr'], 2, ',', '.') }}</td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">Belum ada data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Box below table 1 -->
                            <div class="mt-3 p-3" style="background-color: #dbeafe; border-radius: 8px;">
                                <p class="mb-0" style="font-size: 13px;">
                                    Hasil perhitungan NFR menunjukkan bahwa wilayah dengan nilai netflow negatif juga
                                    memiliki nilai NFR negatif, yang mengonfirmasi perannya sebagai daerah asal pergerakan.
                                </p>
                            </div>
                            <div class="mt-3 p-3"
                                style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <p class="mb-0" style="font-size: 13px;">
                                    Dapat dilihat bahwa
                                    <strong>{{ isset($top_origin_nfr[0]) ? $top_origin_nfr[0]['name'] : 'Kota Administrasi Jakarta Utara' }}</strong>
                                    mencatat nilai NFR paling rendah, yaitu sebesar
                                    <strong>{{ isset($top_origin_nfr[0]) ? number_format($top_origin_nfr[0]['nfr'], 2, ',', '.') : '0,19' }}</strong>.
                                    nilai ini mengindikasikan bahwa <strong>arus pergerakan keluar dari wilayah ini secara
                                        relatif lebih dominan</strong> dibandingkan arus pergerakan masuk, sehingga
                                    cenderung berperan sebagain daerah asal pergerakan.
                                </p>
                            </div>
                        </div>

                        <!-- Right Table -->
                        <div class="col-lg-6">
                            <h6 class="fw-bold text-center mb-3">20 Besar Kabupaten/Kota Tujuan Nasional berdasarkan
                                kekuatan NFR</h6>
                            <div class="table-responsive border rounded border-custom-thick">
                                <table class="table table-bordered table-striped table-custom mb-0 text-center">
                                    <thead>
                                        <tr>
                                            <th style="width: 30px;">Rank</th>
                                            <th class="text-start">Kabupaten/Kota</th>
                                            <th class="text-end" style="font-size: 10px;">Pergerakan Keluar<br>(Outflow)
                                            </th>
                                            <th class="text-end" style="font-size: 10px;">Pergerakan Masuk<br>(Inflow)
                                            </th>
                                            <th class="text-end">Netflow</th>
                                            <th>NFR</th>
                                            <th>Ket</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($top_dest_nfr as $index => $row)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="text-start">{{ $row['name'] }}</td>
                                                <td class="text-end">{{ number_format($row['outflow'], 0, ',', '.') }}
                                                </td>
                                                <td class="text-end">{{ number_format($row['inflow'], 0, ',', '.') }}</td>
                                                <td class="text-end">
                                                    @if ($row['netflow'] < 0)
                                                        - {{ number_format(abs($row['netflow']), 0, ',', '.') }}
                                                    @else
                                                        {{ number_format($row['netflow'], 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($row['nfr'], 2, ',', '.') }}</td>
                                                <td>{{ $row['keterangan'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-3 text-muted">Belum ada data</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Box below table 2 -->
                            <div class="mt-3 p-3"
                                style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <p class="mb-0" style="font-size: 13px;">
                                    Hasil perhitungan NFR menunjukkan bahwa
                                    <strong>{{ isset($top_dest_nfr[0]) ? $top_dest_nfr[0]['name'] : 'Kota Batu' }}</strong>
                                    mencatat nilai NFR tertinggi, yaitu sebesar
                                    <strong>{{ isset($top_dest_nfr[0]) ? number_format($top_dest_nfr[0]['nfr'], 2, ',', '.') : '0,30' }}</strong>.
                                    nilai ini mengindikasikan bahwa arus pergerakan masuk ke
                                    {{ isset($top_dest_nfr[0]) ? $top_dest_nfr[0]['name'] : 'Kota Batu' }} lebih dominan
                                    dibandingkan arus keluar, hal ini menunjukkan
                                    {{ isset($top_dest_nfr[0]) ? $top_dest_nfr[0]['name'] : 'Kota Batu' }} sebagai daerah
                                    tujuan pergerakan yang relative sangat kuat.
                                </p>
                            </div>
                            <div class="mt-3 p-3"
                                style="background-color: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                                <p class="mb-0" style="font-size: 13px;">
                                    Nilai NFR yang relatif tinggi dan positif pada <strong>Kota Batu, Kab.Gunungkidul, dan
                                        Kota Yogyakarta</strong> sejalan dengan karakteristik ketiga wilayah tersebut
                                    sebagai daerah tujuan wisata selama periode libur Panjang.
                                </p>
                            </div>
                            <p class="mt-2 text-muted" style="font-size: 11px; font-style: italic;">
                                Disclaimer : Keterkaitan antara nilai NFR dan karakteristik wilayah sebagai destinasi wisata
                                dalam analisis ini bersifat indikatif dan tidak dimaksudkan untuk menunjukkan hubungan
                                sebab-akibat secara langsung
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof AOS !== 'undefined') {
                AOS.init({
                    once: true,
                    offset: 50,
                    duration: 600
                });
            }
        });
    </script>
@endpush
