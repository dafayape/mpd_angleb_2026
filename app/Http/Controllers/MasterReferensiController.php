<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterReferensiController extends Controller
{
    /**
     * Referensi Provinsi — data dari ref_provinces (seeder)
     */
    public function provinsi(Request $request)
    {
        $query = DB::table('ref_provinces')->orderBy('code');

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('code', 'ilike', $s)
                  ->orWhere('name', 'ilike', $s);
            });
        }

        $data = $query->paginate(50)->withQueryString();

        return view('master.referensi.provinsi', compact('data'));
    }

    /**
     * Referensi Kabupaten/Kota — data dari ref_cities + join ref_provinces
     */
    public function kabkota(Request $request)
    {
        $query = DB::table('ref_cities')
            ->leftJoin('ref_provinces', 'ref_cities.province_code', '=', 'ref_provinces.code')
            ->select('ref_cities.code', 'ref_cities.name', 'ref_cities.province_code', 'ref_provinces.name as province_name')
            ->orderBy('ref_cities.code');

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('ref_cities.code', 'ilike', $s)
                  ->orWhere('ref_cities.name', 'ilike', $s)
                  ->orWhere('ref_provinces.name', 'ilike', $s);
            });
        }

        if ($request->filled('province_code')) {
            $query->where('ref_cities.province_code', $request->province_code);
        }

        $data      = $query->paginate(50)->withQueryString();
        $provinces = DB::table('ref_provinces')->orderBy('name')->get();

        return view('master.referensi.kabkota', compact('data', 'provinces'));
    }

    /**
     * Referensi Moda Transportasi — data dari ref_transport_modes (seeder)
     */
    public function moda()
    {
        $data = DB::table('ref_transport_modes')->orderBy('code')->paginate(50);

        return view('master.referensi.moda', compact('data'));
    }

    /**
     * Referensi Simpul — tabel ada tapi data kosong (null) untuk saat ini
     */
    public function simpul(Request $request)
    {
        $query = DB::table('ref_transport_nodes')
            ->select('code', 'name', 'category', 'sub_category')
            ->orderBy('code');

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(function ($q) use ($s) {
                $q->where('code', 'ilike', $s)
                  ->orWhere('name', 'ilike', $s)
                  ->orWhere('category', 'ilike', $s);
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $data = $query->paginate(50)->withQueryString();

        return view('master.referensi.simpul', compact('data'));
    }
}
