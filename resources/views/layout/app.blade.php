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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    @stack('css')
    @stack('styles')

    <style>
        *,
        body,
        h1, h2, h3, h4, h5, h6,
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

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect" id="vertical-menu-btn">
                        <i class="fa fa-fw fa-bars"></i>
                    </button>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect">
                        MPD ANGKUTAN LEBARAN 2026
                    </button>
                </div>

                <div class="d-flex">
                    <div class="dropdown d-none d-lg-inline-block ms-1">
                        <button type="button" class="btn header-item noti-icon waves-effect" data-bs-toggle="fullscreen">
                            <i class="bx bx-fullscreen"></i>
                        </button>
                    </div>

                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <div class="rounded-circle header-profile-user bg-soft-primary d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <span class="text-primary fw-bold font-size-14">{{ strtoupper(substr(Auth::user()->name ?? 'User', 0, 2)) }}</span>
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
                            <a href="{{ route('dashboard') }}" class="waves-effect">
                                <i class="bx bx-home-circle"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-bar-chart-alt-2"></i>
                                <span>Grafik MPD</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li class="menu-title" style="padding-left:0;">Nasional</li>
                                <li><a href="{{ route('grafik-mpd.nasional.pergerakan') }}">Pergerakan</a></li>
                                <li><a href="{{ route('grafik-mpd.nasional.od-provinsi') }}">O-D Provinsi</a></li>
                                <li><a href="{{ route('grafik-mpd.nasional.top-kabkota') }}">Top Kabupaten/Kota</a></li>
                                <li><a href="{{ route('grafik-mpd.nasional.mode-share') }}">Mode Share</a></li>
                                <li><a href="{{ route('grafik-mpd.nasional.simpul') }}">Simpul</a></li>
                                <li class="menu-title" style="padding-left:0;">Jabodetabek</li>
                                <li><a href="{{ route('grafik-mpd.jabodetabek.pergerakan-orang') }}">Pergerakan & Orang</a></li>
                                <li><a href="{{ route('grafik-mpd.jabodetabek.pergerakan-orang-opsel') }}">Pergerakan & Orang (Opsel)</a></li>
                                <li><a href="{{ route('grafik-mpd.jabodetabek.od-kabkota') }}">O-D Kabupaten Kota</a></li>
                                <li><a href="{{ route('grafik-mpd.jabodetabek.mode-share') }}">Mode Share</a></li>
                                <li><a href="{{ route('grafik-mpd.jabodetabek.simpul') }}">Simpul</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-detail"></i>
                                <span>Data MPD Opsel</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li class="menu-title" style="padding-left:0;">Nasional</li>
                                <li><a href="{{ route('data-mpd.nasional.pergerakan') }}">Pergerakan</a></li>
                                <li><a href="{{ route('data-mpd.nasional.mode-share') }}">Mode Share</a></li>
                                <li><a href="{{ route('data-mpd.nasional.od-simpul') }}">O-D Simpul</a></li>
                                <li class="menu-title" style="padding-left:0;">Jabodetabek</li>
                                <li><a href="{{ route('data-mpd.jabodetabek.pergerakan') }}">Pergerakan</a></li>
                                <li><a href="{{ route('data-mpd.jabodetabek.mode-share') }}">Mode Share</a></li>
                                <li><a href="{{ route('data-mpd.jabodetabek.od-simpul') }}">O-D Simpul</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="{{ route('map-monitor') }}" class="waves-effect">
                                <i class="bx bx-map"></i>
                                <span>Map Monitor</span>
                            </a>
                        </li>
                        <li class="menu-title">EXECUTIVE SUMMARY</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-file"></i>
                                <span>Laporan</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('executive.daily-report') }}">Daily Report</a></li>
                                <li><a href="{{ route('executive.summary') }}">Summary</a></li>
                            </ul>
                        </li>

                        <li class="menu-title">Master</li>

                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-file"></i>
                                <span>Referensi</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="{{ route('master.referensi.provinsi') }}">Provinsi</a></li>
                                <li><a href="{{ route('master.referensi.kabkota') }}">Kabupaten Kota</a></li>
                                <li><a href="{{ route('master.referensi.simpul') }}">Simpul</a></li>
                                <li><a href="{{ route('master.referensi.moda') }}">Moda</a></li>
                            </ul>
                        </li>

                        <li>
                            <a href="{{ route('pengguna') }}" class="waves-effect">
                                <i class="bx bx-user"></i>
                                <span>Pengguna</span>
                            </a>
                        </li>

                        <li class="menu-title">Datasource</li>

                        <li>
                            <a href="{{ route('datasource.upload') }}" class="waves-effect">
                                <i class="bx bx-upload"></i>
                                <span>Upload File (xlsx)</span>
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

                        <li class="menu-title">System & Monitoring</li>

                        <li>
                            <a href="{{ route('log-aktivitas') }}" class="waves-effect">
                                <i class="bx bx-history"></i>
                                <span>Log Aktivitas</span>
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
                            <script>document.write(new Date().getFullYear())</script> &copy; BKT-KEMENHUB (V-1.5)
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Kementerian Perhubungan Republik Indonesia
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
                title: '{{ session("success") }}',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        @endif
    </script>

    @stack('scripts')
</body>

</html>
