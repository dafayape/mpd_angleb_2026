import sys

content = """                    <div class="table-responsive-xl mb-0">
                        <table class="table table-bordered calendar-grid-table min-w-1000 mb-0">
                            <thead>
                                <tr>
                                    <th width="14.28%">Senin</th>
                                    <th width="14.28%">Selasa</th>
                                    <th width="14.28%">Rabu</th>
                                    <th width="14.28%">Kamis</th>
                                    <th width="14.28%">Jumat</th>
                                    <th width="14.28%">Sabtu</th>
                                    <th width="14.28%">Minggu</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Week 1: 19 - 22 Feb -->
                                <tr>
                                    <td colspan="3" class="bg-empty-day border-0"></td>
                                    <td>
                                        <div class="calendar-date-header">19 Februari</div>
                                        <ol class="calendar-task-list">
                                            <li>Tahap persiapan dan koordinasi</li>
                                            <li>Meeting dengan tim IT</li>
                                            <li>Penyampaian timeline, target, dan deliverables</li>
                                        </ol>
                                    </td>
                                    <td><div class="calendar-date-header">20 Februari</div></td>
                                    <td><div class="calendar-date-header">21 Februari</div></td>
                                    <td><div class="calendar-date-header">22 Februari</div></td>
                                </tr>
                                <!-- Week 2: 23 Feb - 1 Mar -->
                                <tr class="bg-light">
                                    <th>23 Februari</th>
                                    <th>24 Februari</th>
                                    <th>25 Februari</th>
                                    <th>26 Februari</th>
                                    <th>27 Februari</th>
                                    <th>28 Februari</th>
                                    <th>1 Maret</th>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <ol class="calendar-task-list">
                                            <li>Penyusunan daftar kebutuhan analisis dan output</li>
                                            <li>Persiapan pengembangan sistem oleh tim IT</li>
                                        </ol>
                                    </td>
                                    <td colspan="3">
                                        <ol class="calendar-task-list">
                                            <li>Sinkronisasi substansi</li>
                                            <li>Review keynote material yang akan ditampilkan di dalam dashboard</li>
                                        </ol>
                                    </td>
                                    <td colspan="2" class="align-middle">
                                        Review progres pengembangan sistem dan validasi kesesuaian data yang ada di sistem dengan kebutuhan analisis
                                    </td>
                                </tr>
                                <!-- Week 3: 2 Mar - 8 Mar -->
                                <tr class="bg-light">
                                    <th>2 Maret</th>
                                    <th>3 Maret</th>
                                    <th>4 Maret</th>
                                    <th>5 Maret</th>
                                    <th class="bg-beta-ready">6 Maret</th>
                                    <th>7 Maret</th>
                                    <th>8 Maret</th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <ol class="calendar-task-list">
                                            <li>Uji coba dan finalisasi sistem</li>
                                            <li>Pengolahan data dummy yang diterima dari Opsel</li>
                                            <li>Validasi kesesuaian data yang ada di sistem dengan kebutuhan analisis dan output yang diharapkan</li>
                                        </ol>
                                    </td>
                                    <td class="bg-beta-ready align-middle text-center">
                                        <strong>Target sistem sudah siap 100%</strong><br>
                                        <small>(Beta Version)</small>
                                    </td>
                                    <td colspan="2" class="bg-empty-day border-0"></td>
                                </tr>
                                <!-- Week 4: 9 Mar - 15 Mar -->
                                <tr class="bg-light">
                                    <th>9 Maret</th>
                                    <th>10 Maret</th>
                                    <th>11 Maret</th>
                                    <th>12 Maret</th>
                                    <th class="bg-posko-period">13 Maret</th>
                                    <th class="bg-posko-period">14 Maret</th>
                                    <th class="bg-posko-period">15 Maret</th>
                                </tr>
                                <tr>
                                    <td colspan="4">
                                        <ol class="calendar-task-list">
                                            <li>Uji coba (<em>beta testing</em>) dan <em>final cross-check</em> data</li>
                                            <li><em>Cross-check</em> kesesuaian data dan output-nya (tabel, grafik, desire line, dll)</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-1<br>(H-8 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-2<br>(H-7 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-3<br>(H-6 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                </tr>
                                
                                <!-- Week 5: 16 Mar - 22 Mar -->
                                <tr class="bg-posko-period border-top">
                                    <th>16 Maret</th><th>17 Maret</th><th>18 Maret</th><th>19 Maret</th><th>20 Maret</th><th>21 Maret</th><th>22 Maret</th>
                                </tr>
                                <tr class="bg-posko-period">
                                    @php
                                        $w5_dates = [
                                            ["day" => "Hari-4", "h" => "H-5 Lebaran"],
                                            ["day" => "Hari-5", "h" => "H-4 Lebaran"],
                                            ["day" => "Hari-6", "h" => "H-3 Lebaran"],
                                            ["day" => "Hari-7", "h" => "H-2 Lebaran"],
                                            ["day" => "Hari-8", "h" => "H-1 Lebaran"],
                                            ["day" => "Hari-9", "h" => "Hari Pertama Lebaran"],
                                            ["day" => "Hari-10", "h" => "H+1 Lebaran"],
                                        ];
                                    @endphp
                                    @foreach($w5_dates as $d)
                                    <td>
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD {{ $d["day"] }}<br>({{ $d["h"] }})</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    @endforeach
                                </tr>
                                
                                <!-- Week 6: 23 Mar - 29 Mar -->
                                <tr class="bg-posko-period border-top">
                                    <th>23 Maret</th><th>24 Maret</th><th>25 Maret</th><th>26 Maret</th><th>27 Maret</th><th>28 Maret</th><th>29 Maret</th>
                                </tr>
                                <tr class="bg-posko-period">
                                    @php
                                        $w6_dates = [
                                            ["day" => "Hari-11", "h" => "H+2 Lebaran"],
                                            ["day" => "Hari-12", "h" => "H+3 Lebaran"],
                                            ["day" => "Hari-13", "h" => "H+4 Lebaran"],
                                            ["day" => "Hari-14", "h" => "H+5 Lebaran"],
                                            ["day" => "Hari-15", "h" => "H+6 Lebaran"],
                                            ["day" => "Hari-16", "h" => "H+7 Lebaran"],
                                            ["day" => "Hari-17", "h" => "H+8 Lebaran"],
                                        ];
                                    @endphp
                                    @foreach($w6_dates as $d)
                                    <td>
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD {{ $d["day"] }}<br>({{ $d["h"] }})</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    @endforeach
                                </tr>
                                
                                <!-- Week 7: 30 Mar - 5 Apr -->
                                <tr class="bg-light">
                                    <th class="bg-posko-period">30 Maret</th>
                                    <th class="bg-posko-period">31 Maret</th>
                                    <th>1 April</th>
                                    <th>2 April</th>
                                    <th>3 April</th>
                                    <th>4 April</th>
                                    <th>5 April</th>
                                </tr>
                                <tr>
                                    <td class="bg-posko-period">
                                        <ol class="calendar-task-list">
                                            <li>Penarikan data MPD Hari-18<br>(H+9 Lebaran)</li>
                                            <li>QC data harian (data anomali, data tidak lengkap, dan lainnya)</li>
                                            <li>Monitoring kestabilan sistem</li>
                                        </ol>
                                    </td>
                                    <td class="bg-posko-period align-middle">
                                        Finalisasi laporan hasil olah data MPD Angleb 2026
                                    </td>
                                    <td class="align-middle">
                                        Finalisasi laporan hasil olah data MPD Angleb 2026
                                    </td>
                                    <td class="align-middle">
                                        Penyusunan Dokumen Policy Paper dan Policy Brief
                                    </td>
                                    <td class="align-middle">
                                        Penyusunan Dokumen Policy Paper dan Policy Brief
                                    </td>
                                    <td colspan="2" class="bg-empty-day border-0"></td>
                                </tr>
                                
                                <!-- Week 8: 6 Apr - 12 Apr -->
                                <tr class="bg-light">
                                    <th>6 April</th>
                                    <th>7 April</th>
                                    <th>8 April</th>
                                    <th>9 April</th>
                                    <th class="bg-deliverable">10 April</th>
                                    <th>11 April</th>
                                    <th>12 April</th>
                                </tr>
                                <tr>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="align-middle">Penyusunan Dokumen Policy Paper dan Policy Brief</td>
                                    <td class="bg-deliverable align-middle text-center">
                                        <strong>Penyampaian Seluruh Deliverables ke BKT</strong>
                                    </td>
                                    <td colspan="2" class="bg-empty-day border-0"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>"""

with open("/home/dafayape/Documents/raw_bkt/mpd_angleb_2026/resources/views/dashboard/index.blade.php", "r") as f:
    lines = f.readlines()

# The timeline block is between lines 24 and 230
new_lines = lines[:24] + [content + "\n"] + lines[230:]

with open("/home/dafayape/Documents/raw_bkt/mpd_angleb_2026/resources/views/dashboard/index.blade.php", "w") as f:
    f.writelines(new_lines)

print("Done")
