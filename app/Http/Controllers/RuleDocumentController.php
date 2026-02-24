<?php

namespace App\Http\Controllers;

use App\Models\RuleDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class RuleDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = RuleDocument::with('uploader');

        if ($request->has('search') && $request->search != '') {
            $query->where('original_name', 'ilike', '%' . $request->search . '%');
        }

        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $documents = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('master.rule-document.index', compact('documents'));
    }

    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin yang dapat mengunggah dokumen.');
        }

        $request->validate([
            'document' => 'required|file|max:102400', // 100MB max
        ]);

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

        return redirect()->back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function download($id)
    {
        $document = RuleDocument::findOrFail($id);
        
        $fullPath = storage_path('app/' . $document->file_path);
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'File tidak ditemukan di server.');
        }

        return response()->download($fullPath, $document->original_name);
    }

    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Hanya admin yang dapat menghapus dokumen.');
        }

        $document = RuleDocument::findOrFail($id);

        if (Storage::disk('local')->exists($document->file_path)) {
            Storage::disk('local')->delete($document->file_path);
        }

        $document->delete();

        return redirect()->back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
