<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    /**
     * Upload new files
     */
    public function store(Request $request)
    {
        \Log::info('=== FILE UPLOAD DEBUG START ===');
        
        try {
            // Kiểm tra xác thực
            $user = auth('sanctum')->user();
            if (!$user) {
                \Log::error('Authentication failed');
                return response()->json([
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            \Log::info('User authenticated: ' . $user->user_id);

            // Validation
            $validator = Validator::make($request->all(), [
                'files' => 'required|array|min:1',
                'files.*' => 'required|file|max:51200', // 50MB max
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $uploadedFiles = [];
            
            foreach ($request->file('files') as $file) {
                $originalName = $file->getClientOriginalName();
                \Log::info('Processing file: ' . $originalName);
                
                // Tạo tên file unique
                $extension = $file->getClientOriginalExtension();
                $fileName = pathinfo($originalName, PATHINFO_FILENAME);
                $uniqueFileName = $fileName . '_' . time() . '_' . uniqid() . '.' . $extension;
                
                // Store file
                $filePath = $file->storeAs('uploads', $uniqueFileName, 'public');
                
                // Save to database
                $fileRecord = [
                    'user_id' => $user->user_id,
                    'file_name' => $originalName,
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                $fileId = DB::table('Files')->insertGetId($fileRecord);
                $uploadedFiles[] = array_merge($fileRecord, ['file_id' => $fileId]);
                
                \Log::info('File uploaded successfully: ' . $originalName . ' -> ' . $filePath);
            }

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'data' => $uploadedFiles
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Exception in file upload: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Upload failed',
                'error_details' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get all files for authenticated user
     */
    public function index()
    {
        try {
            $user = auth('sanctum')->user();
            
            $files = DB::table('Files')
                ->where('user_id', $user->user_id)
                ->whereNull('deleted_at')
                ->orderBy('uploaded_at', 'desc')
                ->get();

            // Get goals and notes linked to each file
            foreach ($files as $file) {
                // Get linked goals
                $file->goals = DB::table('FileGoalLinks')
                    ->join('Goals', 'FileGoalLinks.goal_id', '=', 'Goals.goal_id')
                    ->where('FileGoalLinks.file_id', $file->file_id)
                    ->whereNull('Goals.deleted_at')
                    ->select('Goals.goal_id', 'Goals.title')
                    ->get()
                    ->toArray();

                // Get linked notes
                $file->notes = DB::table('FileNoteLinks')
                    ->join('Notes', 'FileNoteLinks.note_id', '=', 'Notes.note_id')
                    ->where('FileNoteLinks.file_id', $file->file_id)
                    ->whereNull('Notes.deleted_at')
                    ->select('Notes.note_id', 'Notes.title')
                    ->get()
                    ->toArray();
            }

            return response()->json([
                'success' => true,
                'message' => 'Files retrieved successfully',
                'data' => $files
            ]);

        } catch (\Exception $e) {
            \Log::error('Error retrieving files: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show specific file
     */
    public function show($fileId)
    {
        try {
            $user = auth('sanctum')->user();
            
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->user_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found or access denied'
                ], 404);
            }

            // Get linked goals
            $file->goals = DB::table('FileGoalLinks')
                ->join('Goals', 'FileGoalLinks.goal_id', '=', 'Goals.goal_id')
                ->where('FileGoalLinks.file_id', $file->file_id)
                ->whereNull('Goals.deleted_at')
                ->select('Goals.goal_id', 'Goals.title')
                ->get()
                ->toArray();

            // Get linked notes
            $file->notes = DB::table('FileNoteLinks')
                ->join('Notes', 'FileNoteLinks.note_id', '=', 'Notes.note_id')
                ->where('FileNoteLinks.file_id', $file->file_id)
                ->whereNull('Notes.deleted_at')
                ->select('Notes.note_id', 'Notes.title')
                ->get()
                ->toArray();

            return response()->json([
                'success' => true,
                'message' => 'File retrieved successfully',
                'data' => $file
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download file
     */
    public function download($fileId)
    {
        try {
            $user = auth('sanctum')->user();
            
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->user_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found or access denied'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $file->file_path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found on server'
                ], 404);
            }

            return response()->download($filePath, $file->file_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a file
     */
    public function destroy($fileId)
    {
        try {
            $user = auth('sanctum')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }
            
            \Log::info('Attempting to delete file ID: ' . $fileId . ' for user: ' . $user->user_id);
            
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->user_id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                \Log::error('File not found or access denied for file ID: ' . $fileId);
                return response()->json([
                    'success' => false,
                    'message' => 'File not found or access denied'
                ], 404);
            }

            // Xóa các liên kết trước
            DB::table('FileGoalLinks')->where('file_id', $fileId)->delete();
            DB::table('FileNoteLinks')->where('file_id', $fileId)->delete();

            // Soft delete the file record
            DB::table('Files')
                ->where('file_id', $fileId)
                ->update([
                    'deleted_at' => now(),
                    'updated_at' => now()
                ]);

            // Delete the actual file from storage
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            \Log::info('File deleted successfully: ' . $fileId);

            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error deleting file: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show file with all its links
     */
    public function showWithLinks($fileId)
    {
        try {
            $user = Auth::user();
            
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Get linked goals
            $file->goals = DB::table('FileGoalLinks')
                ->join('Goals', 'FileGoalLinks.goal_id', '=', 'Goals.goal_id')
                ->where('FileGoalLinks.file_id', $file->file_id)
                ->whereNull('Goals.deleted_at')
                ->select('Goals.goal_id', 'Goals.title', 'Goals.description')
                ->get();

            // Get linked notes
            $file->notes = DB::table('FileNoteLinks')
                ->join('Notes', 'FileNoteLinks.note_id', '=', 'Notes.note_id')
                ->where('FileNoteLinks.file_id', $file->file_id)
                ->whereNull('Notes.deleted_at')
                ->select('Notes.note_id', 'Notes.title', 'Notes.content')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $file,
                'message' => 'File with links retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Link file to goal
     */
    public function linkGoal(Request $request, $fileId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'goal_id' => 'required|integer|exists:Goals,goal_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Check if file exists and belongs to user
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Check if goal belongs to user or user is collaborator
            $goal = DB::table('Goals')
                ->leftJoin('GoalCollaboration', function($join) use ($user) {
                    $join->on('Goals.goal_id', '=', 'GoalCollaboration.goal_id')
                         ->where('GoalCollaboration.user_id', $user->id);
                })
                ->where('Goals.goal_id', $request->goal_id)
                ->where(function($query) use ($user) {
                    $query->where('Goals.user_id', $user->id)
                          ->orWhereNotNull('GoalCollaboration.collab_id');
                })
                ->whereNull('Goals.deleted_at')
                ->first();

            if (!$goal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Goal not found or access denied'
                ], 404);
            }

            // Check if link already exists
            $existingLink = DB::table('FileGoalLinks')
                ->where('file_id', $fileId)
                ->where('goal_id', $request->goal_id)
                ->first();

            if ($existingLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is already linked to this goal'
                ], 400);
            }

            // Create the link
            DB::table('FileGoalLinks')->insert([
                'file_id' => $fileId,
                'goal_id' => $request->goal_id,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File linked to goal successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error linking file to goal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlink file from goal
     */
    public function unlinkGoal($fileId, $goalId)
    {
        try {
            $user = Auth::user();
            
            // Check if file exists and belongs to user
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Remove the link
            $deleted = DB::table('FileGoalLinks')
                ->where('file_id', $fileId)
                ->where('goal_id', $goalId)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'File unlinked from goal successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unlinking file from goal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Link file to note
     */
    public function linkNote(Request $request, $fileId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'note_id' => 'required|integer|exists:Notes,note_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();
            
            // Check if file exists and belongs to user
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Check if note belongs to user
            $note = DB::table('Notes')
                ->where('note_id', $request->note_id)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$note) {
                return response()->json([
                    'success' => false,
                    'message' => 'Note not found'
                ], 404);
            }

            // Check if link already exists
            $existingLink = DB::table('FileNoteLinks')
                ->where('file_id', $fileId)
                ->where('note_id', $request->note_id)
                ->first();

            if ($existingLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is already linked to this note'
                ], 400);
            }

            // Create the link
            DB::table('FileNoteLinks')->insert([
                'file_id' => $fileId,
                'note_id' => $request->note_id,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'File linked to note successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error linking file to note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unlink file from note
     */
    public function unlinkNote($fileId, $noteId)
    {
        try {
            $user = Auth::user();
            
            // Check if file exists and belongs to user
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Remove the link
            $deleted = DB::table('FileNoteLinks')
                ->where('file_id', $fileId)
                ->where('note_id', $noteId)
                ->delete();

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Link not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'File unlinked from note successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error unlinking file from note: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get trashed files
     */
    public function trashed()
    {
        try {
            $user = Auth::user();
            
            $trashedFiles = DB::table('Files')
                ->where('user_id', $user->id)
                ->whereNotNull('deleted_at')
                ->orderBy('deleted_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $trashedFiles,
                'message' => 'Trashed files retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving trashed files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a trashed file
     */
    public function restore($fileId)
    {
        try {
            $user = Auth::user();
            
            $file = DB::table('Files')
                ->where('file_id', $fileId)
                ->where('user_id', $user->id)
                ->whereNotNull('deleted_at')
                ->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Trashed file not found'
                ], 404);
            }

            DB::table('Files')
                ->where('file_id', $fileId)
                ->update([
                    'deleted_at' => null,
                    'updated_at' => now()
                ]);

            return response()->json([
                'success' => true,
                'message' => 'File restored successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error restoring file: ' . $e->getMessage()
            ], 500);
        }
    }
}
