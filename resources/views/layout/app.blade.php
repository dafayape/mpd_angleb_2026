<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'BKT-KEMENHUB') @yield('subtitle', '')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    @stack('css')
    @stack('styles')

    <style>
        .vertical-menu {
            z-index: 1002 !important;
        }

        .navbar-brand-box {
            z-index: 1003 !important;
        }

        .vertical-menu .simplebar-content-wrapper {
            height: 100% !important;
            overflow-y: auto !important;
            padding-bottom: 50px !important;
        }

        #sidebar-menu {
            padding-bottom: 80px;
        }

        body.sidebar-enable .vertical-menu {
            box-shadow: 0 0 24px 0 rgba(0, 0, 0, 0.06), 0 1px 0 0 rgba(0, 0, 0, 0.02);
        }

        @media (max-width: 991.98px) {
            .vertical-menu {
                position: fixed;
                top: 70px;
                bottom: 0;
                height: calc(100vh - 70px);
                overflow-y: auto;
                display: none;
            }

            body.sidebar-enable .vertical-menu {
                display: block !important;
            }

            .navbar-brand-box {
                z-index: 1003;
            }

            #sidebar-menu {
                padding-top: 10px;
                padding-bottom: 100px;
            }
        }

        .metismenu li {
            white-space: normal !important;
        }

        .metismenu li a {
            padding-right: 10px;
        }

        .simplebar-track.simplebar-vertical {
            width: 8px;
            background-color: transparent;
        }

        .simplebar-scrollbar:before {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }
    </style>
</head>

<body data-sidebar="dark">

    <div id="layout-wrapper">

        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <div class="navbar-brand-box">
                        <a href="/" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-only.png') }}" alt="" height="27">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="" height="27">
                            </span>
                        </a>

                        <a href="/" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-only.png') }}" alt="" height="27">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="" height="27">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect">
                        @php
                            $tahun = session('tahun_periode');
                            $judul = match (session('kategori_data')) {
                                'lebaran' => 'LEBARAN TAHUN ' . $tahun,
                                'nataru' => 'NATAL DAN TAHUN BARU ' . $tahun . '/' . $tahun + 1,
                                default => '<i class="fa fa-home></i>',
                            };
                        @endphp
                        {!! $judul ?? '' !!}
                    </button>
                </div>

                <div class="d-flex">
                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                            <i class="bx bx-fullscreen"></i>
                        </button>
                    </div>

                    @php
                        $photo = Auth::user()->photo ?? null;
                        $photoPath = $photo ? public_path('storage/photos/' . $photo) : null;
                    @endphp

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="{{ ($photo && file_exists($photoPath)) ? asset('storage/photos/' . $photo) : asset('assets/images/users/avatar-1.jpg') }}" alt="User Avatar">
                            <span class="d-none d-xl-inline-block ms-1" key="t-henry">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bx bx-user font-size-16 align-middle me-1"></i>
                                <span key="t-profile">Profile</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                            <a class="dropdown-item text-danger" href="#" id="btnLogout">
                                <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i>
                                <span key="t-logout">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">
                        @php
                            $kategori = session('kategori_data');
                            $sumber = session('sumber_data');
                            $isAnglebMpd = $kategori == 'lebaran' && $sumber == 'mpd';
                        @endphp

                        @if ($isAnglebMpd)
                            <li class="menu-title" key="t-menu">DATASOURCE</li>
                            <li class="d-none">
                                <a href="{{ route('angleb.mpd.upload') }}" class="waves-effect">
                                    <i class="bx bx-upload"></i>
                                    <span key="t-upload-file">Upload File (.csv)</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('angleb.mpd.history') }}" class="waves-effect">
                                    <i class="bx bx-history"></i>
                                    <span key="t-history-file">History File Upload</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('angleb.mpd.raw-data') }}" class="waves-effect">
                                    <i class="bx bx-table"></i>
                                    <span key="t-view-raw-data">View Raw Data</span>
                                </a>
                            </li>

                            <li class="menu-title" key="t-menu">MASTER</li>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-file"></i>
                                    <span key="t-referensi">Referensi</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ route('angleb.mpd.master.provinsi') }}" key="t-provinsi">Provinsi</a></li>
                                    <li><a href="{{ route('angleb.mpd.master.kabkota') }}" key="t-kabkota">Kabupaten atau Kota</a></li>
                                    <li><a href="{{ route('angleb.mpd.master.simpul') }}" key="t-simpul">Simpul</a></li>
                                    <li><a href="{{ route('angleb.mpd.master.moda') }}" key="t-moda">Moda Transportasi</a></li>
                                </ul>
                            </li>
                        @else
                            @if (in_array($kategori, ['lebaran']))
                                <li class="menu-title" key="t-menu">Survey</li>
                                <li>
                                    <a href="{{ url('angleb/survey') }}" class="waves-effect">
                                        <i class="bx bx-home"></i>
                                        <span key="t-dashboards-survey">Dashboard Survey</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-detail"></i>
                                        <span key="t-dashboards">Analisis Nasional</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ url('angleb/survey/profil-responden') }}" key="t-default">A1 Profil Pelaku Perjalanan</a></li>
                                        <li><a href="{{ url('angleb/survey/rencana-mudik-tidak') }}" key="t-default">A2 Rencana Mudik/Tidak</a></li>
                                        <li><a href="{{ url('angleb/survey/alasan-tidak-bepergian') }}" key="t-default">A3 Alasan Tidak Bepergian</a></li>
                                        <li><a href="{{ url('angleb/survey/alasan-bepergian') }}" key="t-default">A4 Alasan Bepergian</a></li>
                                        <li><a href="{{ url('angleb/survey/daerah-asal') }}" key="t-default">A5 Asal Total</a></li>
                                        <li><a href="{{ url('angleb/survey/daerah-tujuan') }}" key="t-default">A6 Tujuan Total</a></li>
                                        <li><a href="{{ url('angleb/survey/jumlah-orang-ikut') }}" key="t-default">A7 Jumlah Orang Ikut Bepergian</a></li>
                                        <li><a href="{{ url('angleb/survey/hari-pergi-dan-pulang') }}" key="t-default">A8 Pilihan Hari Pergi Dan Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/jam-pergi-dan-pulang') }}" key="t-default">A9 Pilihan Jam Pergi Dan Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/lama-tinggal-dilokasi-tujuan') }}" key="t-default">A10 Lama Tinggal Dilokasi Tujuan</a></li>
                                        <li><a href="{{ url('angleb/survey/dana-yang-dihabiskan') }}" key="t-default">A11 Dana Yang Dihabiskan</a></li>
                                        <li><a href="{{ url('angleb/survey/pertimbangan-pilihan-moda') }}" key="t-default">A12 Alasan/Pertimbangan Pemilihan Moda</a></li>
                                        <li><a href="{{ url('angleb/survey/pilihan-moda-berangkat') }}" key="t-default">A13 Pilihan Moda Berangkat</a></li>
                                        <li><a href="{{ url('angleb/survey/pilihan-moda-pulang') }}" key="t-default">A14 Pilihan Moda Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/jumlah-orang-ikut-balik') }}" key="t-default">A15 Tambahan Orang Balik</a></li>
                                        <li><a href="{{ url('angleb/survey/alasan-batal-perjalanan') }}" key="t-default">A16 Faktor Eksogen Batal Pergi</a></li>
                                        <li><a href="{{ url('angleb/survey/setuju-tidak-wfa-cb') }}" key="t-default">A17 Setuju/Tidak WFA/CB</a></li>
                                        <li><a href="{{ url('angleb/survey/pilihan-kebijakan') }}" key="t-default">A18 Pilihan Kebijakan</a></li>
                                        <li><a href="{{ url('angleb/survey/pilihan-hari-wfa-pergi') }}" key="t-default">A19 Pilihan Hari WFA Pergi</a></li>
                                        <li><a href="{{ url('angleb/survey/dampak-wfa-pergi') }}" key="t-default">A20 Dampak WFA Pergi</a></li>
                                        <li><a href="{{ url('angleb/survey/pilihan-hari-wfa-pulang') }}" key="t-default">A21 Pilihan Hari WFA Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/dampak-wfa-terhadap-kepulangan') }}" key="t-default">A22 Dampak WFA Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/lebaran-tahun-lalu') }}" key="t-default">A23 Perjalanan Lebaran Tahun Lalu</a></li>
                                        <li><a href="{{ url('angleb/survey/evaluasi-lebaran-tahun-lalu') }}" key="t-default">A24 Evaluasi Lebaran Tahun Lalu</a></li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-detail"></i>
                                        <span key="t-dashboards">Analisis Jabodetabek</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ url('angleb/survey/jabodetabek/profil-responden') }}" key="t-default">A1 Profil Responden</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/rencana-mudik-tidak') }}" key="t-default">A2 Rencana Mudik/Tidak</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/alasan-tidak-bepergian') }}" key="t-default">A3 Alasan Tidak Bepergian</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/alasan-bepergian') }}" key="t-default">A4 Alasan Bepergian</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/top-10-asal') }}" key="t-default">A5 Daerah Asal</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/top-10-tujuan') }}" key="t-default">A6 Daerah Tujuan</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/jumlah-orang-ikut-bepergian') }}" key="t-default">A7 Jumlah Orang Ikut</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/pilihan-hari-pergi-dan-pulang') }}" key="t-default">A8 Pilihan Hari Pergi Dan Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/pilihan-jam-pergi-dan-pulang') }}" key="t-default">A9 Pilihan Jam Pergi Dan Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/lama-tinggal-di-lokasi-tujuan') }}" key="t-default">A10 Lama Tinggal Dilokasi</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/dana-yang-dihabiskan') }}" key="t-default">A11 Dana Yang Dihabiskan</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/alasan-pertimbangan-pilihan-moda') }}" key="t-default">A12 Alasan/Pertimbangan Pilihan Moda</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/pilihan-moda-berangkat') }}" key="t-default">A13 Pilihan Moda Berangkat</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/pilihan-moda-pulang') }}" key="t-default">A14 Pilihan Moda Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/jumlah-orang-ikut-balik') }}" key="t-default">A15 Jumlah Orang Ikut Balik</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/alasan-batal-bepergian') }}" key="t-default">A16 Faktor Eksogen Batal Bepergian</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/setuju-tidak-wfa-cb') }}" key="t-default">A17 Setuju/Tidak WFA/CB</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/pilihan-kebijakan') }}" key="t-default">A18 Pilihan Kebijakan</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/pilihan-hari-wfa-pergi') }}" key="t-default">A19 Pilihan Hari WFA Pergi</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/dampak-wfa-pergi') }}" key="t-default">A20 Dampak WFA Pergi</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/pilihan-hari-wfa-pulang') }}" key="t-default">A21 Pilihan Hari WFA Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/dampak-wfa-pulang') }}" key="t-default">A22 Dampak WFA Pulang</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/perjalanan-lebaran-tahun-lalu') }}" key="t-default">A23 Perjalanan Lebaran Tahun Lalu</a></li>
                                        <li><a href="{{ url('angleb/survey/jabodetabek/evaluasi-lebaran-tahun-lalu') }}" key="t-default">A24 Evaluasi Lebaran Tahun Lalu</a></li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-bulb"></i>
                                        <span key="t-dashboards">Rekomendasi</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ url('angleb/survey/rekomendasi/nasional') }}">Nasional</a></li>
                                        <li><a href="{{ url('angleb/survey/rekomendasi/jabodetabek') }}">Jabodetabek</a></li>
                                        <li><a href="{{ url('angleb/survey/rekomendasi/pulaujawa') }}">Per Provinsi Pulau Jawa</a></li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="{{ url('angleb/survey/upload') }}" class="waves-effect">
                                        <i class="bx bx-file"></i>
                                        <span key="t-file-manager">Upload Survey</span>
                                    </a>
                                </li>

                                <li class="menu-title d-none" key="t-menu">MPD</li>
                                <li class="d-none">
                                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-home"></i>
                                        <span key="t-dashboards-mpd">Dashboard MPD</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ url('angleb/mpd') }}" key="t-dashboard-mpd-nasional">Nasional</a></li>
                                        <li><a href="{{ url('angleb/mpd/jabodetabek') }}" key="t-dashboard-mpd-jabodetabek">Jabodetabek</a></li>
                                    </ul>
                                </li>
                            @endif

                            @if (in_array($kategori, ['nataru']))
                                <li class="menu-title" key="t-menu">Survey</li>

                                <li>
                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-home"></i>
                                        <span key="t-dashboards">Dashboard Survey</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ route('nataru.survey.dashboard.nasional') }}" key="t-default">Nasional</a></li>
                                        <li><a href="{{ route('nataru.survey.dashboard.jabodetabek') }}" key="t-saas">Jabodetabek</a></li>
                                    </ul>
                                </li>

                                <li>
                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-pie-chart-alt-2"></i>
                                        <span key="t-layouts">Hasil Survey</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow" key="t-vertical">Nasional</a>
                                            <ul class="sub-menu" aria-expanded="true">
                                                <li><a href="{{ route('nataru.survey.profil.pelaku.perjalanan.nasional') }}" key="t-light-sidebar">Profil Responden</a></li>
                                                <li><a href="{{ route('nataru.survey.seluruh.moda.perjalanan.nasional') }}" key="t-compact-sidebar">Intensi Pejalanan Seluruh Moda</a></li>
                                                <li><a href="{{ route('nataru.survey.sepuluh.besar.daerah.asal.dan.tujuan.nasional') }}" key="t-icon-sidebar">10 Besar Daerah Asal dan Tujuan</a></li>
                                                <li><a href="{{ route('nataru.survey.sepuluh.besar.kabkot.asal.dan.tujuan.nasional') }}" key="t-boxed-width">10 Besar Kabupaten/Kota Asal dan Tujuan</a></li>
                                                <li><a href="{{ route('nataru.survey.od.provinsi.nasional') }}" key="t-mode-share">O-D Provinsi</a></li>
                                                <li><a href="{{ route('nataru.survey.mode.share.nasional') }}" key="t-mode-share">Mode Share Nasional</a></li>
                                                <li><a href="{{ route('nataru.survey.prediksi.pilihan.moda.nasional') }}" key="t-prediksi-pilihan-moda">Prediksi Pilihan Moda Nasional</a></li>
                                                <li><a href="{{ route('nataru.survey.perkiraan.biaya.dihabiskan.nasional') }}" key="t-perkiraan-biaya-dihabiskan">Perkiraan Biaya yang Dihabiskan</a></li>
                                                <li><a href="{{ route('nataru.survey.hari.dan.jam.keberangkatan.nasional') }}" key="t-hari-dan-jam-keberangkatan-nasional">Hari dan Jam Keberangkatan</a></li>
                                                <li><a href="{{ route('nataru.survey.hari.dan.jam.kepulangan.nasional') }}" key="t-hari-dan-jam-kepulangan-nasional">Hari dan Jam Kepulangan</a></li>
                                                <li><a href="{{ route('nataru.survey.pilihan.rute.nasional') }}" key="t-pilihan-rute-nasional">Pilihan Rute Nasional</a></li>
                                                <li><a href="{{ route('nataru.survey.kepemilikan.tiket.nasional') }}" key="t-kepemilikan-tiket-nasional">Kepemilikan Tiket</a></li>
                                                <li><a href="{{ route('nataru.survey.simpul.transportasi.terpadat.nasional') }}" key="t-smpul-terpadat-nasional">Simpul Transportasi Terpadat</a></li>
                                                <li><a href="{{ route('nataru.survey.sumber.akses.link.nasional') }}" key="t-smpul-terpadat-nasional">Sumber Akses Link</a></li>
                                            </ul>
                                        </li>

                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow" key="t-horizontal">Jabodetabek</a>
                                            <ul class="sub-menu" aria-expanded="false">
                                                <li><a href="{{ route('nataru.survey.intensi.perjalanan.jabodetabek') }}" key="t-intensi-jabodetabek">Intensi Perjalanan Jabodetabek</a></li>
                                                <li><a href="{{ route('nataru.survey.od.perjalanan.jabodetabek') }}" key="t-od-jabodetabek">O-D Jabodetabek</a></li>
                                                <li><a href="{{ route('nataru.survey.pilihan.moda.jabodetabek') }}" key="t-mode-share">Mode Share Jabodetabek</a></li>
                                                <li><a href="{{ route('nataru.survey.perkiraan.biaya.dihabiskan.jabodetabek') }}" key="t-perkiraan-biaya">Perkiraan Biaya Dihabiskan</a></li>
                                                <li><a href="{{ route('nataru.survey.kepemilikan.tiket.jabodetabek') }}" key="t-jabodetabek-tiket">Kepemilikan Tiket</a></li>
                                                <li><a href="{{ route('nataru.survey.sumber.akses.link.jabodetabek') }}" key="t-jabodetabek-akses-link">Sumber Akses Link</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-title" key="t-apps">MPD</li>
                                <li>
                                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-home"></i>
                                        <span key="t-dashboards-mpd">Dashboard MPD</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ url('mpd/dashboard') }}" key="t-dashboard-nasional">Nasional</a></li>
                                        <li><a href="{{ url('mpd/dashboard/jabodetabek') }}" key="t-dashboard-jabodetabek">Jabodetabek</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="javascript:void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-detail"></i>
                                        <span key="t-dashboards">Data MPD Opsel</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ url('mpd/data/pergerakan') }}" key="t-default">Pergerakan</a></li>
                                        <li><a href="{{ url('mpd/data/mode-share') }}" key="t-default">Mode Share</a></li>
                                        <li><a href="{{ url('mpd/data/od-simpul') }}" key="t-default">OD Simpul</a></li>
                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <span key="t-vertical-jabodetabek">Jabodetabek</span>
                                            </a>
                                            <ul class="sub-menu" aria-expanded="false">
                                                <li><a href="{{ url('mpd/data/jabodetabek/pergerakan') }}" key="t-tui-jabodetabek-pergerakan">Pergerakan</a></li>
                                                <li><a href="{{ url('mpd/data/jabodetabek/modeshare') }}" key="t-tui-jabodetabek-pergerakan">Mode Share</a></li>
                                                <li><a href="{{ url('mpd/data/jabodetabek/odsimpul') }}" key="t-tui-jabodetabek-pergerakan">OD Simpul</a></li>
                                            </ul>
                                        </li>
                                    </ul>
                                </li>

                                <li class="menu-title" key="t-apps">Datasource</li>

                                <li>
                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-data"></i>
                                        <span key="t-vertical">Survey</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ route('nataru.survey.upload.index') }}" key="t-tui-calendar">Upload File (xlsx)</a></li>
                                        <li><a href="{{ route('nataru.survey.history.index') }}" key="t-full-calendar">History File Upload</a></li>
                                        <li><a href="{{ route('nataru.survey.dataraw.index') }}" key="t-full-calendar">View Raw Data</a></li>
                                    </ul>
                                </li>
                                <li>
                                    <a href="javascript: void(0);" class="has-arrow waves-effect">
                                        <i class="bx bx-data"></i>
                                        <span key="t-vertical">MPD Opsel</span>
                                    </a>
                                    <ul class="sub-menu" aria-expanded="false">
                                        <li><a href="{{ url('mpd/upload') }}" key="t-tui-calendar">Upload File (xlsx)</a></li>
                                        <li><a href="{{ url('mpd/upload/history-file') }}" key="t-full-calendar">History File Upload</a></li>
                                        <li><a href="{{ url('mpd/upload/data') }}" key="t-full-calendar">View Raw Data</a></li>
                                    </ul>
                                </li>
                            @endif

                            <li class="menu-title" key="t-apps">Master</li>

                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-file"></i>
                                    <span key="t-vertical-ref">Referensi</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ route('mpd.populasi.index') }}" key="t-tui-populasi">Populasi Wilayah</a></li>
                                    <li><a href="{{ route('mpd.posko-angleb.index') }}" key="t-tui-posko-angleb">Posko Angleb 2026</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-user"></i>
                                    <span key="t-vertical-akun">Akun</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ route('users.index') }}" key="t-tui-calendar">Pengguna</a></li>
                                </ul>
                            </li>

                            <li class="menu-title" key="t-apps">System & Monitoring</li>
                            <li>
                                <a href="{{ route('angleb.survey.activity.log.index') }}" class="waves-effect">
                                    <i class="bx bx-history"></i>
                                    <span key="t-file-manager">Log Aktivitas</span>
                                </a>
                            </li>

                            @if(auth()->check() && auth()->user()->role == 'admin')
                                <li class="menu-title" key="t-menu">DATA SOURCES</li>
                                <li>
                                    <a href="{{ route('angleb.survey.simulasi') }}" class="waves-effect">
                                        <i class="bx bx-upload"></i>
                                        <span key="t-upload-simulasi">Upload Simulasi</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('angleb.mpd.history') }}" class="waves-effect">
                                        <i class="bx bx-history"></i>
                                        <span key="t-history-simulasi">History Simulasi</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('angleb.mpd.raw-data') }}" class="waves-effect">
                                        <i class="bx bx-table"></i>
                                        <span key="t-raw-data-simulasi">Raw Data View</span>
                                    </a>
                                </li>
                            @endif
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content" style="background-color : #fbfdff !important;">
                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © BKT-KEMENHUB (V-1.5)
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Kementrian Perhubungan Republik Indonesia
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

    </div>

    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

    <script src="{{ asset('assets/js/app.js') }}"></script>

    @stack('js')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('btnLogout').addEventListener('click', function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Yakin ingin logout?',
                text: "Sesi kamu akan berakhir.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        });

        @if (session('success'))
            Swal.fire({
                toast: true,
                position: 'top',
                icon: 'success',
                title: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif
    </script>
    @stack('scripts')
</body>

</html>
