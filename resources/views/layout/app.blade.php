<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'BKT-KEMENHUB') @yield('subtitle', '')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Mobile Positioning Data - Kementerian Perhubungan RI" />
    <meta name="author" content="BKT Kemenhub" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    @stack('css')
    @stack('styles')

    <style>
        *,
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        .navbar-header,
        .vertical-menu,
        .footer,
        .dropdown-menu,
        .card,
        .btn,
        .breadcrumb-item,
        .breadcrumb-item a,
        #sidebar-menu .menu-title,
        #sidebar-menu ul li a,
        .page-title-box h4 {
            font-family: 'Poppins', sans-serif;
        }

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

        /* Global Utilities */
        .bg-navy {
            background-color: #1a3353 !important;
        }

        .text-navy {
            color: #1a3353 !important;
        }

        .page-header-container {
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #1a3353;
        }

        .page-header-number {
            font-size: 26px;
            font-weight: 700;
            background-color: #e5e9f0;
            color: #4b5563;
            padding: 15px 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Poppins', sans-serif;
            border-right: 1px solid #1a3353;
        }

        .page-header-title-bar {
            background-color: #213c5f;
            color: #ffffff;
            padding: 15px 24px;
        }

        .page-header-title-bar .title-text {
            font-family: 'Poppins', sans-serif;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .page-header-extra .breadcrumb {
            background-color: transparent !important;
            padding: 0 !important;
            margin-bottom: 0 !important;
            font-size: 14px;
        }

        .page-header-extra .breadcrumb-item a {
            color: rgba(255, 255, 255, 0.5);
            /* Faded white for parent links */
            text-decoration: none;
            font-weight: 400;
        }

        .page-header-extra .breadcrumb-item a:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .page-header-extra .breadcrumb-item.active {
            color: rgba(255, 255, 255, 0.7);
            /* Slightly brighter for active link */
            font-weight: 400;
        }

        .page-header-extra .breadcrumb-item+.breadcrumb-item::before {
            color: rgba(255, 255, 255, 0.5);
            content: "/";
            padding: 0 8px;
        }

        .placeholder-page-card {
            min-height: 350px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px dashed #ced4da;
            box-shadow: none !important;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .page-header-container {
                flex-direction: column !important;
            }

            .page-header-number {
                border-right: none;
                border-bottom: 1px solid #1a3353;
                padding: 10px;
                border-radius: 6px 6px 0 0;
            }

            .page-header-title-bar {
                flex-direction: column !important;
                align-items: flex-start !important;
                padding: 12px 15px;
            }

            .page-header-extra {
                margin-top: 10px !important;
                text-align: left !important;
                margin-left: 0 !important;
            }
        }

        .placeholder-page-card {
            min-height: 350px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px dashed #ced4da;
            box-shadow: none !important;
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
                                <img src="{{ asset('assets/images/logo-only.png') }}" alt="Logo" height="27">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" height="27">
                            </span>
                        </a>
                        <a href="/" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('assets/images/logo-only.png') }}" alt="Logo" height="27">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" height="27">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect">
                        MPD ANGKUTAN LEBARAN 2026
                    </button>
                </div>

                <div class="d-flex">
                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button type="button" class="btn header-item noti-icon waves-effect"
                            data-bs-toggle="fullscreen">
                            <i class="bx bx-fullscreen"></i>
                        </button>
                    </div>

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="rounded-circle header-profile-user bg-soft-primary d-inline-flex align-items-center justify-content-center"
                                style="width: 36px; height: 36px;">
                                <span
                                    class="text-primary fw-bold font-size-14">{{ strtoupper(substr(Auth::user()->name ?? 'User', 0, 2)) }}</span>
                            </div>
                            <span class="d-none d-xl-inline-block ms-1">{{ Auth::user()->name ?? 'Pengguna' }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bx bx-user font-size-16 align-middle me-1"></i>
                                <span>Profil</span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="#" id="btnLogout">
                                <i class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i>
                                <span>Logout</span>
                            </a>
                        </div>
                    </div>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </div>
            </div>
        </header>

        <div class="vertical-menu">
            <div data-simplebar class="h-100">
                <div id="sidebar-menu">
                    <ul class="metismenu list-unstyled" id="side-menu">

                        <li class="menu-title">MPD</li>

                        <li>
                            <a href="{{ route('keynote') }}" class="waves-effect">
                                <i class="bx bx-slideshow"></i>
                                <span>Keynote Material</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('dashboard') }}" class="waves-effect">
                                <i class="bx bx-home-circle"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="menu-title">Nasional</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                <span>Pergerakan Nasional</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('pages.nasional.data-dasar') }}">Data Dasar</a></li>
                                <li><a href="{{ route('pages.nasional.pergerakan-harian') }}">Pergerakan Harian</a>
                                </li>
                                <li><a href="{{ route('pages.nasional.od') }}">Origin-Destination (OD)</a></li>
                                <li><a href="{{ route('pages.nasional.mode-share') }}">Mode Share</a></li>
                            </ul>
                        </li>

                        <li class="menu-title">Jabodetabek</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                <span>Intra</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('pages.jabodetabek.intra-pergerakan') }}">Pergerakan Harian</a>
                                </li>
                                <li><a href="{{ route('pages.jabodetabek.intra-od') }}">Origin-Destination (OD)</a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                <span>Inter</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('pages.jabodetabek.inter-pergerakan') }}">Pergerakan Harian</a>
                                </li>
                                <li><a href="{{ route('pages.jabodetabek.inter-od') }}">Origin-Destination (OD)</a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="{{ route('pages.substansi.netflow') }}" class="waves-effect">
                                <i class="bx bx-transfer"></i>
                                <span>Netflow</span>
                            </a>
                        </li>


                        <li class="menu-title">Kesimpulan dan Rekomendasi</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                <span>Kesimpulan</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('pages.kesimpulan.nasional') }}">Nasional</a></li>
                                <li><a href="{{ route('pages.kesimpulan.jabodetabek') }}">Jabodetabek</a></li>

                            </ul>
                        </li>

                        <li class="menu-title">Substansi Tambahan</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                <span>Simpul Transportasi</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('pages.substansi.stasiun-ka-antar-kota') }}">Stasiun KA Antar
                                        Kota</a></li>
                                <li><a href="{{ route('pages.substansi.pelabuhan-penyeberangan') }}">Pelabuhan
                                        Penyeberangan</a></li>
                                <li><a href="{{ route('pages.substansi.pelabuhan-laut') }}">Pelabuhan Laut</a></li>
                                <li><a href="{{ route('pages.substansi.bandara') }}">Bandara</a></li>
                                <li><a href="{{ route('pages.substansi.terminal') }}">Terminal</a></li>
                                <li><a href="{{ route('pages.substansi.od-simpul-pelabuhan') }}">Origin-Destination
                                        (OD) Simpul Pelabuhan</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="{{ route('pages.kesimpulan.rekomendasi') }}" class="waves-effect">
                                <i class="bx bx-file"></i>
                                <span>Rekomendasi</span>
                            </a>
                        </li>

                        <li class="menu-title">Geofencing</li>

                        <li>
                            <a href="{{ route('map-monitor') }}" class="waves-effect">
                                <i class="bx bx-map"></i>
                                <span>Map Monitor</span>
                            </a>
                        </li>

                        <li class="menu-title">Executive Summary</li>

                        <li>
                            <a href="{{ route('executive.daily-report') }}" class="waves-effect">
                                <i class="bx bx-file"></i>
                                <span>Daily Report</span>
                            </a>
                        </li>

                        <li class="menu-title">Master Data</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-file"></i>
                                <span>Tabel Referensi</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('master.referensi.provinsi') }}">Provinsi</a></li>
                                <li><a href="{{ route('master.referensi.kabkota') }}">Kabupaten Kota</a></li>
                                <li><a href="{{ route('master.referensi.simpul') }}">Simpul</a></li>
                                <li><a href="{{ route('master.referensi.moda') }}">Moda</a></li>
                                <li><a href="{{ route('master.rule-document.index') }}">Dokumentasi Teknis</a></li>
                            </ul>
                        </li>

                        <li class="menu-title">Datasource</li>

                        <li>
                            <a href="{{ route('datasource.upload') }}" class="waves-effect">
                                <i class="bx bx-upload"></i>
                                <span>Upload File (CSV)</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('datasource.history') }}" class="waves-effect">
                                <i class="bx bx-history"></i>
                                <span>History File Upload</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('datasource.raw-data') }}" class="waves-effect">
                                <i class="bx bx-table"></i>
                                <span>View Raw Data</span>
                            </a>
                        </li>

                        <li class="menu-title">Sistem Monitoring</li>

                        <li>
                            <a href="{{ route('pengguna') }}" class="waves-effect">
                                <i class="bx bx-user"></i>
                                <span>Pengguna</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('log-aktivitas') }}" class="waves-effect">
                                <i class="bx bx-history"></i>
                                <span>Log Aktivitas</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('devlog') }}" class="waves-effect">
                                <i class="bx bx-code-alt"></i>
                                <span>Log Developer</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('pengaturan') }}" class="waves-effect">
                                <i class="bx bx-cog"></i>
                                <span>Pengaturan</span>
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="page-content" style="background-color: #fbfdff !important;">
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
                            </script> &copy; BKT-KEMENHUB (v1.5.2)
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Badan Kebijakan Transportasi - Kementerian Perhubungan Republik Indonesia
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
                text: 'Sesi kamu akan berakhir.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then(function(result) {
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
                title: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif
    </script>

    @stack('scripts')
</body>

</html>
