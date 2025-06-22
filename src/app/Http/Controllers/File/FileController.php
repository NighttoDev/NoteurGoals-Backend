<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    public function index()
    {
        $files = Auth::user()->files()->paginate(10);
        return response()->json($files);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $uploaded = $request->file('file');
        $path = $uploaded->store('uploads', 'public');

        $file = File::create([
            'user_id' => Auth::id(),
            'file_name' => $uploaded->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $uploaded->getClientMimeType(),
            'file_size' => $uploaded->getSize(),
        ]);

        return response()->json(['message' => 'File uploaded', 'file' => $file], 201);
    }

    public function show(File $file)
    {
        if ($file->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json($file);
    }

    public function destroy(File $file)
    {
        if ($file->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return response()->json(['message' => 'File deleted']);
    }
}
