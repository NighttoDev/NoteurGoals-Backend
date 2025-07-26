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
    public function index(Request $request)
    {
        $files = Auth::user()->files()
            ->orderBy('uploaded_at', 'desc')
            ->paginate(10);

        return response()->json($files);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // 10MB max
            'file_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $uploadedFile = $request->file('file');
        $fileName = $request->file_name ?: $uploadedFile->getClientOriginalName();
        $filePath = $uploadedFile->store('uploads', 'public');

        $file = File::create([
            'user_id' => Auth::user()->user_id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $uploadedFile->getMimeType(),
            'file_size' => $uploadedFile->getSize(),
            'uploaded_at' => now(),
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'file' => $file
        ], 201);
    }

    public function show(File $file)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($file);
    }

    public function destroy(File $file)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete file from storage
        Storage::disk('public')->delete($file->file_path);

        $file->delete();

        return response()->json([
            'message' => 'File deleted successfully'
        ]);
    }

    // Goal linking methods
    public function linkGoal(Request $request, File $file)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'goal_id' => 'required|exists:Goals,goal_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal = \App\Models\Goal::find($request->goal_id);
        
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($file->linkGoal($request->goal_id)) {
            return response()->json(['message' => 'Goal linked to file successfully']);
        } else {
            return response()->json(['message' => 'Goal is already linked to this file'], 409);
        }
    }

    public function unlinkGoal(File $file, $goalId)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($file->unlinkGoal($goalId)) {
            return response()->json(['message' => 'Goal unlinked from file successfully']);
        } else {
            return response()->json(['message' => 'Goal was not linked to this file'], 404);
        }
    }

    // Note linking methods
    public function linkNote(Request $request, File $file)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'note_id' => 'required|exists:Notes,note_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $note = \App\Models\Note::find($request->note_id);
        
        if ($note->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($file->linkNote($request->note_id)) {
            return response()->json(['message' => 'Note linked to file successfully']);
        } else {
            return response()->json(['message' => 'Note is already linked to this file'], 409);
        }
    }

    public function unlinkNote(File $file, $noteId)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($file->unlinkNote($noteId)) {
            return response()->json(['message' => 'Note unlinked from file successfully']);
        } else {
            return response()->json(['message' => 'Note was not linked to this file'], 404);
        }
    }

    // Get file with all its links
    public function showWithLinks(File $file)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($file->load(['goals', 'notes']));
    }

    // Download file
    public function download(File $file)
    {
        if ($file->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!Storage::disk('public')->exists($file->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }
}
