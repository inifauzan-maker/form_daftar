@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-8">
    <h2 class="text-2xl font-bold mb-6">Arsip Konten</h2>
    <div class="bg-white shadow rounded-lg p-6">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Publish</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">File</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contents as $content)
                <tr>
                    <td class="px-4 py-2">{{ $content->judul }}</td>
                    <td class="px-4 py-2">{{ $content->tanggal_publish }}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs {{ $content->status == 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                            {{ ucfirst($content->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        @if($content->file)
                        <a href="{{ asset('storage/' . $content->file) }}" target="_blank" class="text-blue-700 underline">Lihat File</a>
                        @else
                        <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        <a href="{{ route('konten-marketing.show', $content->id) }}" class="text-blue-700 hover:underline">Review</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $contents->links() }}
        </div>
    </div>
</div>
@endsection
