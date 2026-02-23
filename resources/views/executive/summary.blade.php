@extends('layout.app')

@section('content')
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h4 class="mb-0 font-size-18">Executive Summary - Ringkasan Analisis</h4>
            <div class="d-flex flex-column flex-md-row gap-2">
                <form class="d-flex align-items-center gap-2" id="filterForm">
                    <input type="date" id="startDate" class="form-control" value="2026-03-13" min="2026-03-13"
                        max="2026-03-29">
                    <span class="text-muted fw-bold">&mdash;</span>
                    <input type="date" id="endDate" class="form-control" value="2026-03-29" min="2026-03-13"
                        max="2026-03-29">
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
                        <h5 class="card-title mb-0 text-primary">
                            <i class="bx bx-bot align-middle me-1"></i> AI-Generated Insights
                        </h5>
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
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Total Target Paparan</h6>
                                <h3 class="mb-0 text-primary fw-bold" id="sum-paparan">0</h3>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Total Realisasi Aktual</h6>
                                <h3 class="mb-0 text-success fw-bold" id="sum-aktual">0</h3>
                                <span id="sum-badge" class="badge bg-success mt-2 font-size-13 py-1 px-3"></span>
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

                loading.classList.remove('d-none');
                content.classList.add('d-none');
                error.classList.add('d-none');

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
                    if (data.analysis && data.analysis.length > 0) {
                        let html = '';
                        data.analysis.forEach((point, index) => {
                            const formatted = point.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                            html += `<p class="text-justify mb-3">${formatted}</p>`;
                        });
                        aiContainer.innerHTML = html;
                    } else {
                        aiContainer.innerHTML =
                            '<p class="text-muted text-center italic">Sistem tidak memiliki data yang cukup untuk ditarik kesimpulannya pada rentang tanggal tersebut.</p>';
                    }

                    loading.classList.add('d-none');
                    content.classList.remove('d-none');

                } catch (err) {
                    console.error(err);
                    loading.classList.add('d-none');
                    error.classList.remove('d-none');
                }
            }

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
