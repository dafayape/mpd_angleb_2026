<?php

namespace App\Http\Controllers;

use App\Models\RuleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RuleDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = RuleDocument::with('uploader');

        // Gunakan filled() untuk memastikan nilainya tidak null/kosong
        if ($request->filled('search')) {
            $query->where('original_name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('master.rule-document.index', compact('documents'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya admin yang dapat mengunggah dokumen.',
                ], 403);
            }

            return redirect()->back()->with('error', 'Hanya admin yang dapat mengunggah dokumen.');
        }

        $request->validate([
            'document' => 'required|file|max:102400', // 100MB max
        ]);

        try {
            $file = $request->file('document');
            $originalName = $file->getClientOriginalName();
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('rule-documents', $fileName, 'local');

            RuleDocument::create([
                'file_name' => $fileName,
                'original_name' => $originalName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'uploaded_by' => Auth::id(),
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil diunggah.',
                ]);
            }

            return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Upload Rule Document Error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menyimpan file: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal menyimpan file ke sistem.');
        }
    }

    public function download($id)
    {
        try {
            $document = RuleDocument::findOrFail($id);
            $path = Storage::disk('local')->path($document->file_path);

            if (! file_exists($path)) {
                return redirect()->back()->with('error', 'File tidak ditemukan di direktori server.');
            }

            return response()->download($path, $document->original_name);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Daftar dokumen tidak ditemukan pada database.');
        }
    }

    public function preview($id)
    {
        try {
            $document = RuleDocument::findOrFail($id);
            $path = Storage::disk('local')->path($document->file_path);

            if (! file_exists($path)) {
                return redirect()->back()->with('error', 'File tidak ditemukan di direktori server.');
            }

            return response()->file($path, [
                'Content-Disposition' => 'inline; filename="' . $document->original_name . '"',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'Daftar dokumen tidak ditemukan pada database.');
        }
    }

    public function destroy(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya admin yang dapat menghapus dokumen.',
                ], 403);
            }

            return redirect()->back()->with('error', 'Hanya admin yang dapat menghapus dokumen.');
        }

        try {
            $document = RuleDocument::findOrFail($id);

            // Selalu set file penghapusan walaupun file fisiknya tidak ketemu
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            $document->delete();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Dokumen berhasil dihapus dari sistem.',
                ]);
            }

            return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal: Dokumen tidak ditemukan di sistem.',
                ], 404);
            }
            return redirect()->back()->with('error', 'Gagal: Dokumen tidak ditemukan.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Delete Rule Document Error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem internal: ' . $e->getMessage(),
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Gagal menghapus dokumen.');
        }
    }
}
