@extends('layout.app')

@section('title', $title)

@section('content')
    @component('layout.partials.page-header', ['number' => '12', 'title' => $title])
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
                    <h5 class="fw-bold text-navy mb-0">Kesimpulan Jabodetabek</h5>
                </div>
                <div class="card-body bg-white" style="padding: 2rem 1.5rem;">

                    @php
                        // Intra Peaks
                        $intraPeak1 = isset($data['intra_peak_days'][0]) ? $data['intra_peak_days'][0] : null;
                        $intraPeak2 = isset($data['intra_peak_days'][1]) ? $data['intra_peak_days'][1] : null;

                        $iP1Date = $intraPeak1
                            ? \Carbon\Carbon::parse($intraPeak1->tanggal)->isoFormat('D MMMM YYYY')
                            : '-';
                        $iP1Val = $intraPeak1 ? $intraPeak1->daily_total : 0;

                        $iP2Date = $intraPeak2
                            ? \Carbon\Carbon::parse($intraPeak2->tanggal)->isoFormat('D MMMM YYYY')
                            : '-';
                        $iP2Val = $intraPeak2 ? $intraPeak2->daily_total : 0;

                        $iPctDiff = 0;
                        if ($iP1Val > 0 && $intraPeak2) {
                            $diff = $iP1Val - $iP2Val;
                            $iPctDiff = ($diff / $iP1Val) * 100;
                        }

                        // Inter Peaks
                        $interPeak1 = isset($data['inter_peak_days'][0]) ? $data['inter_peak_days'][0] : null;
                        $interPeak2 = isset($data['inter_peak_days'][1]) ? $data['inter_peak_days'][1] : null;

                        $inP1Date = $interPeak1
                            ? \Carbon\Carbon::parse($interPeak1->tanggal)->isoFormat('D MMMM YYYY')
                            : '-';
                        $inP1Val = $interPeak1 ? $interPeak1->daily_total : 0;

                        $inP2Date = $interPeak2
                            ? \Carbon\Carbon::parse($interPeak2->tanggal)->isoFormat('D MMMM YYYY')
                            : '-';

                        // Cities Origin
                        $topOrigin = $data['top_origin_jabo'];
                        $o1Name = isset($topOrigin[0]) ? $topOrigin[0]->name : '-';
                        $o1Val = isset($topOrigin[0]) ? $topOrigin[0]->city_total : 0;
                        $o2Name = isset($topOrigin[1]) ? $topOrigin[1]->name : '-';
                        $o3Name = isset($topOrigin[2]) ? $topOrigin[2]->name : '-';

                        // Cities Dest
                        $topDest = $data['top_dest_intra_jabo'];
                        $d1Name = isset($topDest[0]) ? $topDest[0]->name : '-';
                        $d1Val = isset($topDest[0]) ? $topDest[0]->city_total : 0;
                        $d2Name = isset($topDest[1]) ? $topDest[1]->name : '-';
                        $d3Name = isset($topDest[2]) ? $topDest[2]->name : '-';

                        // Inter Dest
                        $topInterDest = $data['top_prov_dest_inter_jabo'];
                        $pDest1Name = isset($topInterDest[0]) ? $topInterDest[0]->name : 'Provinsi Jawa Barat';
                    @endphp

                    <!-- Box 1 -->
                    <div class="summary-box">
                        Pola pergerakan masyarakat <strong>Intra Jabodetabek</strong> secara keseluruhan relatif stabil dan
                        menunjukkan fluktuasi yang cukup dinamis dengan kecenderungan sedikit peningkatan di hari-hari
                        libur, khususnya Hari Libur Lebaran. Pergerakan harian intra Jabodetabek mulai mengalami kenaikan
                        menjelang hari libur, yaitu pada <strong>tanggal {{ $iP1Date }}, dengan pergerakan sebesar
                            {{ number_format($iP1Val, 0, ',', '.') }} pergerakan</strong>. Menariknya, puncak pergerakan
                        intra Jabodetabek ini terjadi lebih awal dibandingkan dengan puncak pergerakan Nasional, hal
                        tersebut dapat berkaitan dengan <strong>dominasi pergerakan jarak pendek dan aktivitas domestik
                            perkotaan.</strong>
                    </div>

                    <!-- Box 2 -->
                    <div class="summary-box">
                        Meskipun puncak utama pergerakan intra Jabodetabek terjadi pada tanggal
                        {{ explode(' ', $iP1Date)[0] ?? '-' }} {{ explode(' ', $iP1Date)[1] ?? '-' }}, jumlah pergerakan
                        pada tanggal {{ explode(' ', $iP2Date)[0] ?? '-' }} {{ explode(' ', $iP2Date)[1] ?? '-' }} tercatat
                        tidak berbeda jauh dengan puncak tersebut, hanya berbeda
                        {{ number_format($iPctDiff, 2, ',', '.') }}% lebih rendah dari total pergerakan di tanggal
                        {{ explode(' ', $iP1Date)[0] ?? '-' }} {{ explode(' ', $iP1Date)[1] ?? '-' }}. Hal ini menunjukkan
                        bahwa <strong>{{ explode(' ', $iP2Date)[0] ?? '-' }} {{ explode(' ', $iP2Date)[1] ?? '-' }} juga
                            dapat dikategorikan sebagai puncak kedua pergerakan intra Jabodetabek</strong>
                    </div>

                    <!-- Box 3 -->
                    <div class="summary-box">
                        <strong>Puncak pergerakan tertinggi Masyarakat inter Jabodetabek (dari Jabodetabek ke wilayah
                            Nasional) tercatat pada tanggal {{ $inP1Date }} sebesar
                            {{ number_format($inP1Val, 0, ',', '.') }} pergerakan.</strong> Pola ini menandai adanya
                        kesamaan puncak pergerakan intra jabodetabek pada puncak pertama, dan keselarasan dengan puncak
                        kedua pergerakan Nasional. Sedangkan <strong>puncak kedua tercatat pada tanggal
                            {{ $inP2Date }}</strong> yang menunjukkan adanya gelombang lanjutan pergerakan inter
                        Jabodetabek setelah perayaan.
                    </div>

                    <!-- Box 4 -->
                    <div class="summary-box">
                        <strong>{{ $o1Name }} menjadi daerah asal pergerakan Masyarakat Jabodetabek</strong> yang
                        paling dominan dengan jumlah pergerakan sebanyak <strong>{{ number_format($o1Val, 0, ',', '.') }}
                            pergerakan</strong>, disusul oleh {{ $o2Name }} dan {{ $o3Name }}.
                    </div>

                    <!-- Box 5 -->
                    <div class="summary-box">
                        Sedangkan <strong>daerah tujuan pergerakan Masyarakat intra Jabodetabek yang paling banyak juga
                            tercatat pada {{ $d1Name }}</strong> dengan total pergerakan sebanyak
                        <strong>{{ number_format($d1Val, 0, ',', '.') }} pergerakan</strong>, dan disusul oleh
                        {{ $d2Name }}, dan {{ $d3Name }} . {{ $d1Name }} menjadi tujuan favorit bagi
                        Masyarakat intra Jabodetabek, hal ini dipengaruhi oleh <strong>peran wilayah ini sebagai destinasi
                            wisata jarak dekat bagi Masyarakat Jabodetabek</strong>, khususnya pada periode libur
                    </div>

                    <!-- Box 6 -->
                    <div class="summary-box mb-0">
                        Untuk <strong>pergerakan inter Jabodetabek, {{ $pDest1Name }} menjadi provinsi tujuan utama yang
                            mendominasi pergerakan Masyarakat Jabodetabek ke wilayah lain</strong> selama periode Lebaran
                        2026. hal ini ditunjukkan oleh peran Jawa barat yang menjadi wilayah penyangga sekaligus destinasi
                        rekreasi jarak dekat, seperti Kawasan Puncak, bandung raya, dan wilayah pesisir.
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
