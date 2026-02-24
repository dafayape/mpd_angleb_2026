@extends('layout.app')

@section('content')
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h4 class="mb-0 font-size-18">Executive Summary - Ringkasan Analisis</h4>
            <div class="d-flex flex-column flex-md-row gap-2">
                <form class="d-flex align-items-center gap-2" id="filterForm">
                    <input type="date" id="startDate" class="form-control" value="2026-03-13" min="2026-03-13"
                        max="2026-03-30">
                    <span class="text-muted fw-bold">&mdash;</span>
                    <input type="date" id="endDate" class="form-control" value="2026-03-30" min="2026-03-13"
                        max="2026-03-30">
                    <button type="submit" class="btn btn-primary d-none d-md-block">Terapkan</button>
                    <button type="submit" class="btn btn-primary d-block d-md-none"><i
                            class="mdi mdi-magnify"></i></button>
                </form>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex align-items-center">
                            <h5 class="card-title mb-0 text-primary">
                                <i class="bx bx-bot align-middle me-1"></i> AI-Generated Insights
                            </h5>
                            <button id="btnCopyAnalysis" class="btn btn-sm btn-outline-primary ms-3 rounded-pill d-none"
                                title="Salin Ringkasan">
                                <i class="mdi mdi-content-copy me-1"></i> Salin Teks
                            </button>
                        </div>
                        <div class="text-muted small">Periode: <strong id="periodLabel">-</strong></div>
                    </div>

                    <!-- Loading State -->
                    <div id="loading-state" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Sistem AI sedang menganalisis data pergerakan...</p>
                    </div>

                    <!-- Error State -->
                    <div id="error-state" class="alert alert-danger d-none" role="alert">
                        Gagal memuat analisis data. Silakan coba lagi nanti.
                    </div>

                    <!-- Content State -->
                    <div id="content-state" class="d-none">
                        <div class="row text-center mb-4 pb-4 border-bottom">
                            <div class="col-md-3 mb-3 mb-md-0">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Total Target Paparan</h6>
                                <h3 class="mb-0 text-primary fw-bold" id="sum-paparan">0</h3>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Total Realisasi Aktual</h6>
                                <h3 class="mb-0 text-success fw-bold" id="sum-aktual">0</h3>
                                <span id="sum-badge" class="badge bg-success mt-2 font-size-13 py-1 px-3"></span>
                            </div>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Unique Subscriber</h6>
                                <h3 class="mb-0 text-info fw-bold" id="sum-orang">0</h3>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Jabodetabek (Real)</h6>
                                <h3 class="mb-0 text-warning fw-bold" id="sum-jabo">0</h3>
                            </div>
                        </div>

                        <div id="ai-analysis-content" class="text-dark" style="font-size: 15px; line-height: 1.8;">
                            <!-- AI points will be injected here -->
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            async function fetchData() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const url = `{{ route('executive.summary.data') }}?start_date=${startDate}&end_date=${endDate}`;

                const loading = document.getElementById('loading-state');
                const content = document.getElementById('content-state');
                const error = document.getElementById('error-state');
                const btnCopy = document.getElementById('btnCopyAnalysis');

                loading.classList.remove('d-none');
                content.classList.add('d-none');
                error.classList.add('d-none');
                btnCopy.classList.add('d-none');

                try {
                    const response = await fetch(url);
                    const data = await response.json();

                    if (data.error) throw new Error(data.error);

                    // Update Period Label
                    document.getElementById('periodLabel').textContent = data.period_label;

                    // Update Summary Numbers
                    const sum = data.summary;
                    document.getElementById('sum-paparan').textContent = (sum.total_paparan || 0)
                        .toLocaleString('id-ID');
                    document.getElementById('sum-aktual').textContent = (sum.total_aktual || 0).toLocaleString(
                        'id-ID');
                    document.getElementById('sum-orang').textContent = (sum.total_orang || 0).toLocaleString(
                        'id-ID');
                    document.getElementById('sum-jabo').textContent = (sum.jabo_real || 0).toLocaleString(
                        'id-ID');

                    const badge = document.getElementById('sum-badge');
                    if (sum.persen >= 100) {
                        badge.className = 'badge bg-success mt-2 font-size-13 py-1 px-3';
                        badge.innerText = sum.persen + '% (Melampaui Target)';
                    } else {
                        badge.className = 'badge bg-warning text-dark mt-2 font-size-13 py-1 px-3';
                        badge.innerText = sum.persen + '% (Di Bawah Target)';
                    }

                    // Update AI Text content
                    const aiContainer = document.getElementById('ai-analysis-content');
                    let html = '';

                    // Nasional Analysis
                    if (data.analysis && data.analysis.length > 0) {
                        html +=
                            '<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-chart-line me-1"></i> Kesimpulan Nasional</h6>';
                        data.analysis.forEach((point) => {
                            const formatted = point.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                            html += `<p class="text-justify mb-2">${formatted}</p>`;
                        });
                    }

                    // Jabodetabek Analysis
                    if (data.analysis_jabodetabek && data.analysis_jabodetabek.length > 0) {
                        html += '<hr class="my-4">';
                        html +=
                            '<h6 class="fw-bold text-warning mb-3"><i class="mdi mdi-map-marker-radius me-1"></i> Kesimpulan Jabodetabek</h6>';
                        data.analysis_jabodetabek.forEach((point) => {
                            const formatted = point.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                            html += `<p class="text-justify mb-2">${formatted}</p>`;
                        });
                    }

                    if (html) {
                        aiContainer.innerHTML = html;
                        btnCopy.classList.remove('d-none');
                    } else {
                        aiContainer.innerHTML =
                            '<p class="text-muted text-center italic">Sistem tidak memiliki data yang cukup untuk ditarik kesimpulannya pada rentang tanggal tersebut.</p>';
                        btnCopy.classList.add('d-none');
                    }

                    loading.classList.add('d-none');
                    content.classList.remove('d-none');

                } catch (err) {
                    console.error(err);
                    loading.classList.add('d-none');
                    error.classList.remove('d-none');
                    btnCopy.classList.add('d-none');
                }
            }

            // Copy Context logic
            document.getElementById('btnCopyAnalysis').addEventListener('click', function() {
                const text = document.getElementById('ai-analysis-content').innerText;
                navigator.clipboard.writeText(text).then(() => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Teks berhasil disalin!',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }).catch(err => {
                    console.error('Gagal menyalin: ', err);
                });
            });

            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const s = document.getElementById('startDate');
                const ed = document.getElementById('endDate');
                if (s.value > ed.value) {
                    [s.value, ed.value] = [ed.value, s.value];
                }
                fetchData();
            });

            fetchData();
        });
    </script>
@endpush
