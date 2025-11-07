<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KontenMarketing;
use Illuminate\Support\Facades\Auth;

class KontenMarketingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    // Arsip Konten: show all published content with file review
    public function arsip()
    {
        $contents = KontenMarketing::whereNotNull('file')
            ->orderByDesc('tanggal_posting')
            ->paginate(15);
        return view('arsip_konten', compact('contents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * API untuk menerima dan menyimpan konten dengan file upload
     */
    public function saveContent(Request $request)
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
            'creator' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'data' => $konten]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
