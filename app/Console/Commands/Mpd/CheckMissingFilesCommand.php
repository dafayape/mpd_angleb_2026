<?php

namespace App\Console\Commands\Mpd;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckMissingFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'mpd:check-missing';

    /**
     * The console command description.
     */
    protected $description = 'Cek daftar file CSV MPD (TSEL, IOH, XL) yang belum berhasil di-upload ke sistem.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Memeriksa daftar file yang belum di-upload (13 - 30 Maret 2026)...");

        $mulaiTgl = Carbon::parse('2026-03-13');
        $akhirTgl = Carbon::parse(config('mpd.end_date', '2026-03-29'));
        $opselList = ['TSEL', 'IOH', 'XL'];
        $kategoriList = ['REAL', 'FORECAST'];

        // 1. Ambil data yang sudah SUKSES di upload
        $uploaded = DB::table('import_jobs')
            ->whereIn('status', ['completed', 'completed_with_errors'])
            ->get(['opsel', 'kategori', 'tanggal_data'])
            ->map(function ($row) {
                return strtoupper($row->opsel) . '|' . strtoupper($row->kategori) . '|' . $row->tanggal_data;
            })
            ->toArray();

        $missingFiles = [];
        $totalExpected = 0;
        $totalMissing = 0;

        // 2. Lakukan Pengecekan Kombinasi
        foreach ($opselList as $ops) {
            foreach ($kategoriList as $kat) {
                $curr = $mulaiTgl->copy();
                
                while ($curr->lte($akhirTgl)) {
                    $totalExpected++;
                    $tglStr = $curr->format('Y-m-d');
                    $kombinasiId = $ops . '|' . $kat . '|' . $tglStr;
                    
                    if (!in_array($kombinasiId, $uploaded)) {
                        $totalMissing++;
                        // Format: mpd_{opsel}_{kategori}_{tanggal}.csv
                        $filename = "mpd_" . strtolower($ops) . "_" . strtolower($kat) . "_" . $curr->format('Ymd') . ".csv";
                        
                        $missingFiles[] = [
                            'Tanggal' => $tglStr,
                            'Opsel' => $ops,
                            'Kategori' => $kat,
                            'Prediksi Nama File' => $filename,
                            'Status' => '❌ BELUM UPLOAD'
                        ];
                    }
                    $curr->addDay();
                }
            }
        }

        // 3. Tampilkan Hasilnya
        if (count($missingFiles) > 0) {
            $this->newLine();
            $this->error("🚨 DITEMUKAN {$totalMissing} DARI {$totalExpected} FILE YANG BELUM DI-UPLOAD/BELUM SUKSES:");
            $this->newLine();
            
            // Tampilkan semuanya saja karena berbasis command line
            $this->table(['Tanggal', 'Opsel', 'Kategori', 'Prediksi Nama File', 'Status'], $missingFiles);
            
            $this->newLine();
            $this->warn("Total Missing: {$totalMissing} File");
            
        } else {
            $this->newLine();
            $this->info("🎉 SEMPURNA! Seluruh {$totalExpected} file dari tanggal 13 - 30 Maret (TSEL, IOH, XL) lengkap.");
        }

        return Command::SUCCESS;
    }
}
