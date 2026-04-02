@extends('layout.app')

@section('title', $title)

@push('css')
    <!-- AOS Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">
    <style>
        .bg-navy {
            background-color: #2a3042 !important;
            color: white !important;
        }

        .text-navy {
            color: #2a3042 !important;
        }

        .content-card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .content-card .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
        }

        .content-card .card-body {
            padding: 2rem 1.5rem;
            line-height: 1.7;
            font-size: 1.05rem;
            color: #334155;
        }

        .ai-content p {
            margin-bottom: 1.25rem;
            text-align: justify;
        }

        .ai-content h1,
        .ai-content h2,
        .ai-content h3 {
            color: #1e293b;
            font-weight: 700;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
        }

        .ai-content ul,
        .ai-content ol {
            padding-left: 1.5rem;
            margin-bottom: 1.25rem;
        }

        .ai-content li {
            margin-bottom: 0.5rem;
            text-align: justify;
        }

        .ai-content strong {
            color: #0f172a;
        }
        
        .ai-content blockquote {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
            padding: 1.25rem 1.5rem;
            margin: 1.5rem 0;
            border-radius: 0 8px 8px 0;
            color: #475569;
            font-style: italic;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .ai-content blockquote strong {
            color: #0d6efd;
        }
        
        .ai-content table {
            width: 100%;
            margin-bottom: 1.5rem;
            border-collapse: collapse;
        }
        
        .ai-content th, .ai-content td {
            border: 1px solid #e2e8f0;
            padding: 12px;
        }
        
        .ai-content th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
@endpush

@section('content')
    @component('layout.partials.page-header', ['number' => '13', 'title' => $title])
        <ol class="breadcrumb m-0 mb-0">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="#">Kesimpulan & Rekomendasi</a></li>
            <li class="breadcrumb-item active">Rekomendasi Kebijakan (AI)</li>
        </ol>
    @endcomponent

    <div class="row mb-4 mt-2" data-aos="fade-down" data-aos-duration="600">
        <div class="col-12">
            <div class="card bg-navy text-white rounded-3 border-0 shadow-lg overflow-hidden position-relative">
                <div class="position-absolute end-0 top-0 h-100"
                    style="width: 30%; background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05));"></div>
                <div class="card-body p-4 d-flex align-items-center position-relative z-1">
                    <div class="bg-white rounded p-3 me-4 shadow-sm">
                        <i class="bx bx-bot fs-1 text-primary"></i>
                    </div>
                    <div>
                        <h4 class="mb-2 fw-bold text-white">Rekomendasi Kebijakan Eksekutif (AI Generated)</h4>
                        <p class="mb-0 text-white-50" style="font-size: 1.05rem;">
                            Analisis ini dihasilkan secara <strong>otomatis</strong> oleh <strong>Sistem Kecerdasan Buatan
                                (AI) terintegrasi</strong>
                            berdasarkan sintesis keseluruhan pergerakan data dari Operator Seluler (MPD) selama masa Angleb
                            2026. Memberikan wawasan kebijakan <i>Decision Support System</i> yang informatif, interaktif,
                            dan solutif.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" data-aos="fade-up" data-aos-delay="100">
        <div class="col-12">
            <div class="card content-card w-100 flex-column">
                <div class="card-header bg-white d-flex justify-content-between align-items-center"
                    style="padding: 1.5rem; border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <div class="d-flex align-items-center">
                        <i class="bx bxs-magic-wand text-warning fs-4 me-2"></i>
                        <h5 class="fw-bold text-navy mb-0">Gagasan & Solusi Strategis</h5>
                    </div>
                    <div class="d-flex gap-2" style="font-size: 0.875rem;">
                        <button id="copyAiBtn"
                            class="btn btn-sm btn-outline-primary shadow-sm d-flex align-items-center gap-1"
                            title="Copy Text">
                            <i class="bx bx-copy"></i> Copy Text
                        </button>
                        <button id="exportAiBtn"
                            class="btn btn-sm btn-outline-success shadow-sm d-flex align-items-center gap-1"
                            title="Export .txt">
                            <i class="bx bx-download"></i> Export .txt
                        </button>
                    </div>
                </div>
                <div class="card-body bg-white ai-content">
                    {!! $ai_html !!}
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

            const aiContent = document.querySelector('.ai-content');
            const copyBtn = document.getElementById('copyAiBtn');
            const exportBtn = document.getElementById('exportAiBtn');

            if (aiContent && copyBtn) {
                copyBtn.addEventListener('click', function() {
                    const textToCopy = aiContent.innerText;
                    navigator.clipboard.writeText(textToCopy).then(function() {
                        const originalHtml = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<i class="bx bx-check"></i> Copied!';
                        copyBtn.classList.replace('btn-outline-primary', 'btn-primary');
                        setTimeout(() => {
                            copyBtn.innerHTML = originalHtml;
                            copyBtn.classList.replace('btn-primary', 'btn-outline-primary');
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Could not copy text: ', err);
                    });
                });
            }

            if (aiContent && exportBtn) {
                exportBtn.addEventListener('click', function() {
                    const textToExport = aiContent.innerText;
                    const blob = new Blob([textToExport], {
                        type: 'text/plain;charset=utf-8'
                    });
                    const url = URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'Rekomendasi_Kebijakan_AI.txt';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    URL.revokeObjectURL(url);
                });
            }
        });
    </script>
@endpush
