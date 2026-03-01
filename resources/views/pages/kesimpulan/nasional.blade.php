@extends('layout.app')

@section('title', $title)

@section('content')
    @component('layout.partials.page-header', ['number' => '11', 'title' => $title])
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
            .summary-box {
                background-color: #e2e8f0;
                border-radius: 8px;
                padding: 1.25rem 1.5rem;
                margin-bottom: 1.5rem;
                font-size: 13.5px;
                color: #1e293b;
                line-height: 1.6;
            }

            .summary-box strong {
                color: #0f172a;
            }

            .summary-list {
                margin-top: 0.5rem;
                margin-bottom: 0;
                padding-left: 1.25rem;
            }

            .content-card {
                border: none;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                margin-bottom: 2rem;
            }
        </style>
    @endpush

    <div class="row mt-2" data-aos="fade-up">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header bg-white" style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <h5 class="fw-bold text-navy mb-0">Kesimpulan Nasional</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2rem 1.5rem;">

                    @php
                        // Format dates
                        $startObj = \Carbon\Carbon::parse($dates->first());
                        $endObj = \Carbon\Carbon::parse($dates->last());

                        $perTglA = $startObj->isoFormat('D MMMM YYYY');
                        $perTglB = $endObj->isoFormat('D MMMM YYYY');

                        // Pergerakan
                        $totalPergerakan = $data['total_pergerakan'];
                        $peak1 = isset($data['peak_days'][0]) ? $data['peak_days'][0] : null;
                        $peak2 = isset($data['peak_days'][1]) ? $data['peak_days'][1] : null;

                        $p1Date = $peak1 ? \Carbon\Carbon::parse($peak1->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';
                        $p1Val = $peak1 ? $peak1->daily_total : 0;
                        $p1Pct = $totalPergerakan > 0 ? ($p1Val / $totalPergerakan) * 100 : 0;

                        $p2Date = $peak2 ? \Carbon\Carbon::parse($peak2->tanggal)->isoFormat('dddd, D MMMM YYYY') : '-';
                        $p2Val = $peak2 ? $peak2->daily_total : 0;
                        $p2Pct = $totalPergerakan > 0 ? ($p2Val / $totalPergerakan) * 100 : 0;

                        // Orang
                        $totalOrang = $data['total_orang'];
                        $ratioOrang = $totalOrang > 0 ? number_format($totalPergerakan / $totalOrang, 2, ',', '.') : 0;

                        // Operator Share
                        $opP = $data['operator_stats']['PERGERAKAN'];
                        $opO = $data['operator_stats']['ORANG'];

                        $tselP_pct = $totalPergerakan > 0 ? ($opP['TSEL'] / $totalPergerakan) * 100 : 0;
                        $tselO_pct = $totalOrang > 0 ? ($opO['TSEL'] / $totalOrang) * 100 : 0;

                        $iohP_pct = $totalPergerakan > 0 ? ($opP['IOH'] / $totalPergerakan) * 100 : 0;
                        $iohO_pct = $totalOrang > 0 ? ($opO['IOH'] / $totalOrang) * 100 : 0;

                        $xlP_pct = $totalPergerakan > 0 ? ($opP['XL'] / $totalPergerakan) * 100 : 0;
                        $xlO_pct = $totalOrang > 0 ? ($opO['XL'] / $totalOrang) * 100 : 0;

                        // Origin
                        $topAsal = $data['top_5_prov_asal'];
                        $topTujuan = $data['top_5_prov_tujuan'];

                        $topKotaAsal = $data['top_3_kota_asal'];
                        $topKotaTujuan = $data['top_5_kota_tujuan'];
                    @endphp

                    <!-- Poin 1 -->
                    <div class="summary-box">
                        Pemantauan pergerakan masyarakat pada periode Lebaran (Angleb) 2026 dilakukan melalui pemanfaatan
                        Mobile Positioning Data (MPD) yang diperoleh dari tiga operator seluler, yaitu Telkomsel, Indosat
                        Ooredoo Hutchison (IOH), serta XL/Smartfren. Periode pengamatan yang telah terekam dalam dataset
                        saat ini mencakup tanggal realisasi data tanggal {{ $perTglA }} hingga {{ $perTglB }}
                    </div>

                    <!-- Poin 2 -->
                    <div class="summary-box">
                        Jumlah pergerakan Masyarakat pada periode Angleb 2026, dengan nilai realisasi per tanggal
                        {{ $perTglA }} s.d. {{ $perTglB }} adalah sebanyak
                        <strong>{{ number_format($totalPergerakan, 0, ',', '.') }} pergerakan</strong> dengan puncak
                        pergerakan di hari <strong>{{ $p1Date }}</strong> dan <strong>{{ $p2Date }}</strong>
                        dengan jumlah pergerakan sebanyak <strong>{{ number_format($p1Val, 0, ',', '.') }}</strong> dan
                        <strong>{{ number_format($p2Val, 0, ',', '.') }} pergerakan</strong> atau sekitar
                        <strong>{{ number_format($p1Pct, 2, ',', '.') }}%</strong> dan
                        <strong>{{ number_format($p2Pct, 2, ',', '.') }}%</strong> terhadap akumulasi data realisasi yang
                        diterima
                    </div>

                    <!-- Poin 3 -->
                    <div class="summary-box">
                        Jumlah orang/individu <i>unique subscriber</i> yang melakukan pergerakan pada Periode Angleb 2026,
                        dengan nilai realisasi hingga tanggal {{ $perTglB }} sebanyak
                        <strong>{{ number_format($totalOrang, 0, ',', '.') }} orang</strong> yang bergerak selama periode
                        Angleb. Data ini menunjukkan bahwa terdapat rata-rata lebih dari satu perjalanan per individu selama
                        periode pengamatan, dengan rasio sekitar <strong>{{ $ratioOrang }} kali</strong> perjalanan per
                        orang
                    </div>

                    <!-- Poin 4 -->
                    <div class="summary-box">
                        Berdasarkan kontribusi operator, <strong>Telkomsel mendominasi</strong> perekaman mobilitas dengan
                        menyumbang sekitar <strong>{{ number_format($tselP_pct, 2, ',', '.') }}% dari total
                            pergerakan</strong> dan <strong>{{ number_format($tselO_pct, 2, ',', '.') }}% dari total jumlah
                            orang</strong>, sementara <strong>IOH menyumbang {{ number_format($iohP_pct, 2, ',', '.') }}%
                            pergerakan dan {{ number_format($iohO_pct, 2, ',', '.') }}% jumlah orang</strong>, serta
                        <strong>XL/Smartfren sebesar {{ number_format($xlP_pct, 2, ',', '.') }}% pergerakan dan
                            {{ number_format($xlO_pct, 2, ',', '.') }}% jumlah orang.</strong> Hal ini konsisten dengan
                        pangsa pasar pelanggan masing-masing operator sehingga valid sebagai representasi kondisi mobilitas
                        nasional.
                    </div>

                    <!-- Poin 5 -->
                    <div class="summary-box">
                        <strong>5 besar provinsi asal favorit Nasional</strong> berdasarkan data MPD, terdiri dari :
                        <ol class="summary-list">
                            @foreach ($topAsal as $prov)
                                <li>{{ $prov->name }} dengan estimasi jumlah pergerakan sekitar
                                    {{ number_format($prov->prov_total / 1000000, 2, ',', '.') }} juta pergerakan</li>
                            @endforeach
                        </ol>
                    </div>

                    <!-- Poin 6 -->
                    <div class="summary-box">
                        <strong>5 besar provinsi tujuan favorit Nasional</strong> berdasarkan data MPD, terdiri dari :
                        <ol class="summary-list">
                            @foreach ($topTujuan as $prov)
                                <li>{{ $prov->name }} dengan estimasi jumlah pergerakan sekitar
                                    {{ number_format($prov->prov_total / 1000000, 2, ',', '.') }} juta pergerakan</li>
                            @endforeach
                        </ol>
                    </div>

                    <!-- Poin 7 -->
                    <div class="summary-box">
                        Dominasi pergerakan yang berasal maupun menuju Jawa Barat dapat disebabkan oleh beberapa faktor,
                        seperti factor provinsi dengan jumlah penduduk terbesar di Indonesia, peran Jawa Barat sebagai
                        wilayah penyangga bagi DKI Jakarta dan Jabodetabek menjadikan provinsi tersebut menjadi simpul utama
                        pergerakan baik untuk perjalanan lokal, regional, maupun antar daerah, dan banyaknya destinasi
                        wisata unggulan di Jawa Barat.
                    </div>

                    <!-- Poin 8 -->
                    <div class="summary-box">
                        @php
                            $kotaAsalList = $topKotaAsal
                                ->pluck('name')
                                ->map(function ($item) {
                                    return '<strong>' . $item . '</strong>';
                                })
                                ->toArray();

                            $kotaAsalStr = '';
                            if (count($kotaAsalList) > 1) {
                                $last = array_pop($kotaAsalList);
                                $kotaAsalStr = implode(', ', $kotaAsalList) . ', dan ' . $last;
                            } else {
                                $kotaAsalStr = implode('', $kotaAsalList);
                            }
                        @endphp
                        {!! $kotaAsalStr !!} menempati peringkat teratas sebagai daerah asal pergerakan Masyarakat, hal
                        ini menunjukkan karakteristik Kawasan metropolitan Jabodetabek sebagai pusat konsentrasi penduduk,
                        aktivitas ekonomi, dan titik awal berbagai jenis mobilitas Masyarakat selama periode libur
                    </div>

                    <!-- Poin 9 -->
                    <div class="summary-box">
                        Pada sisi tujuan pergerakan, <strong>5 besar kota/kabupaten tujuan favorit Nasional</strong>
                        berdasarkan data MPD, terdiri dari:
                        <ol class="summary-list">
                            @foreach ($topKotaTujuan as $kota)
                                <li>{{ $kota->name }} dengan estimasi jumlah pergerakan sekitar
                                    {{ number_format($kota->city_total / 1000000, 2, ',', '.') }} juta pergerakan</li>
                            @endforeach
                        </ol>
                    </div>

                    <!-- Poin 10 (Static Motorcycle Mention usually from Mode Share but forced static text as seen on keynote) -->
                    <div class="summary-box mb-0">
                        <strong>Sepeda motor menjadi moda transportasi yang paling mendominasi</strong>, dengan jumlah
                        pergerakan mencapai 153.535.533 pergerakan atau sekitar 63,31% dari total pergerakan nasional.
                        Disusul oleh mode transportasi mobil yang menempati peringkat kedua, dengan jumlah pergerakan
                        sebesar 44.519.107 pergerakan atau 18,36% dari total pergerakan Nasional
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
