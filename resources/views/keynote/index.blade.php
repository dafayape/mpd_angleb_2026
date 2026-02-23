@extends('layout.app')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Keynote Material - Data Aktual vs Paparan</h4>
                <div class="page-title-right">
                    <form class="d-flex align-items-center gap-2" id="filterForm">
                        <label class="mb-0 fw-bold text-nowrap">Periode:</label>
                        <input type="date" id="startDate" class="form-control form-control-sm" value="2026-03-13"
                            min="2026-03-13" max="2026-03-29" style="width: 140px;">
                        <span class="text-muted fw-bold">&mdash;</span>
                        <input type="date" id="endDate" class="form-control form-control-sm" value="2026-03-29"
                            min="2026-03-13" max="2026-03-29" style="width: 140px;">

                        <label class="mb-0 fw-bold text-nowrap ms-2">Opsel:</label>
                        <select id="opselFilter" class="form-select form-select-sm" style="width: 100px;">
                            <option value="">Semua</option>
                            <option value="TSEL">Telkomsel</option>
                            <option value="IOH">Indosat</option>
                            <option value="XL">XL Axiata</option>
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary text-nowrap ms-1">
                            <i class="mdi mdi-magnify me-1"></i>Terapkan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-3 text-center">
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light shadow-sm">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Target (Paparan)</h6>
                                <h4 class="mb-0 text-primary fw-bold" id="sum-paparan">0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light shadow-sm">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Realisasi (Aktual)</h6>
                                <h4 class="mb-0 text-success fw-bold" id="sum-aktual">0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light shadow-sm">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Selisih</h6>
                                <h4 class="mb-0 fw-bold" id="sum-selisih">0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 border rounded bg-light shadow-sm">
                                <h6 class="text-muted mb-1 text-uppercase small fw-bold">Capaian (%)</h6>
                                <h4 class="mb-0 fw-bold" id="sum-persen">0%</h4>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Rincian Per Simpul</h5>
                        <div class="text-muted small">Periode Data: <span id="periodLabel" class="fw-bold">-</span></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped dt-responsive nowrap w-100" id="table-comparison">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 20px;">#</th>
                                    <th>Kode Simpul</th>
                                    <th>Nama Simpul</th>
                                    <th class="text-end">Paparan (Forecast)</th>
                                    <th class="text-end">Aktual</th>
                                    <th class="text-end">Selisih</th>
                                    <th class="text-end">% Capaian</th>
                                </tr>
                            </thead>
                            <tbody id="table-body">
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // Fetch Data
            async function fetchData() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                const opsel = document.getElementById('opselFilter').value;
                const url =
                    `{{ route('keynote.data') }}?start_date=${startDate}&end_date=${endDate}&opsel=${opsel}`;

                const tbody = document.getElementById('table-body');
                tbody.innerHTML =
                    '<tr><td colspan="7" class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></td></tr>';

                try {
                    const response = await fetch(url);
                    const data = await response.json();

                    if (data.error) {
                        alert('Error: ' + data.error);
                        return;
                    }

                    // Update Label
                    document.getElementById('periodLabel').textContent = data.period_label;

                    // Render Table & Summary
                    renderTable(data.table_data, data.summary);

                } catch (error) {
                    console.error('Error fetching data:', error);
                    tbody.innerHTML =
                        '<tr><td colspan="7" class="text-center text-danger py-3">Gagal memuat data.</td></tr>';
                }
            }

            // Render Table
            function renderTable(data, summary) {
                // Update Summary
                document.getElementById('sum-paparan').textContent = (summary.total_paparan || 0).toLocaleString(
                    'id-ID');
                document.getElementById('sum-aktual').textContent = (summary.total_aktual || 0).toLocaleString(
                    'id-ID');
                document.getElementById('sum-selisih').textContent = (summary.selisih || 0).toLocaleString('id-ID');

                const persen = summary.persen || 0;
                const persenElem = document.getElementById('sum-persen');
                persenElem.textContent = persen + '%';
                persenElem.className = 'mb-0 fw-bold ' + (persen >= 100 ? 'text-success' : (persen >= 80 ?
                    'text-warning' : 'text-danger'));

                // Update Table
                const tbody = document.getElementById('table-body');
                if (!data || data.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data.</td></tr>';
                    return;
                }

                let html = '';
                data.forEach((row, index) => {
                    const selisih = row.aktual - row.paparan;
                    const capain = row.paparan > 0 ? (row.aktual / row.paparan) * 100 : 0;
                    const colorClass = selisih >= 0 ? 'text-success' : 'text-danger';

                    html += `
                <tr>
                    <td class="text-center">${index + 1}</td>
                    <td>${row.code}</td>
                    <td class="fw-bold">${row.name}</td>
                    <td class="text-end">${row.paparan.toLocaleString('id-ID')}</td>
                    <td class="text-end fw-bold">${row.aktual.toLocaleString('id-ID')}</td>
                    <td class="text-end ${colorClass}">${selisih.toLocaleString('id-ID')}</td>
                    <td class="text-end">${capain.toFixed(1)}%</td>
                </tr>
            `;
                });
                tbody.innerHTML = html;
            }

            // Event Listeners
            document.getElementById('filterForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const s = document.getElementById('startDate');
                const ed = document.getElementById('endDate');
                if (s.value > ed.value) {
                    [s.value, ed.value] = [ed.value, s.value];
                }
                fetchData();
            });

            document.getElementById('opselFilter').addEventListener('change', fetchData);

            // Initial Load
            fetchData();
        });
    </script>
@endpush
