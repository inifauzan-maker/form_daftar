<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use App\Models\KontenMarketing;

class ContentUploadController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'platform' => 'required|string',
            'scheduledDate' => 'required|date',
            'description' => 'nullable|string',
            'hashtags' => 'nullable|string',
            'file' => 'nullable|file|mimes:jpg,jpeg,mp4,mov|max:20480', // max 20MB
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('konten_files', 'public');
        }

        $konten = KontenMarketing::create([
            'judul_konten' => $request->title,
            'tipe_konten' => $request->type,
            'platform' => $request->platform,
            'tanggal_posting' => $request->scheduledDate,
            'deskripsi' => $request->description,
            'hashtags' => $request->hashtags,
            'status' => 'Draft',
            'file' => $filePath,
            'type' => $request->type,
        ]);

        return response()->json(['success' => true, 'data' => $konten]);
    }
}
