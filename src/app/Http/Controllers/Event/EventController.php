<?php

namespace App\Http\Controllers\Event;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::where('user_id', Auth::user()->user_id)
            ->latest()
            ->paginate(10);

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'event_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::create([
            'user_id' => Auth::user()->user_id,
            'title' => $request->title,
            'description' => $request->description,
            'event_time' => $request->event_time,
        ]);

        return response()->json(['message' => 'Event created successfully', 'event' => $event], 201);
    }

    public function show(Event $event)
    {
        if ($event->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($event->load('goals'));
    }

    public function update(Request $request, Event $event)
    {
        if ($event->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:200',
            'description' => 'nullable|string',
            'event_time' => 'date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->only(['title', 'description', 'event_time']));

        return response()->json(['message' => 'Event updated successfully', 'event' => $event]);
    }

    public function destroy(Event $event)
    {
        if ($event->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->delete();

        return response()->json(['message' => 'Event moved to trash successfully']);
    }
    
        // ===================================================================
        // CÁC CHỨC NĂNG MỚI CHO THÙNG RÁC CỦA EVENT
        // ===================================================================
    
        /**
         * [MỚI] - Hiển thị danh sách các event đã bị xóa mềm.
         */
        public function trashed()
        {
            $trashedEvents = Auth::user()->events()
                                ->onlyTrashed() // <-- Lấy các mục đã bị xóa mềm
                                ->latest('deleted_at') // Sắp xếp theo ngày xóa mới nhất
                                ->paginate(10);
    
            return response()->json($trashedEvents);
        }
    
        /**
         * [MỚI] - Khôi phục một event từ thùng rác.
         */
        public function restore($id)
        {
            // Tìm event TRONG THÙNG RÁC của user để đảm bảo bảo mật
            $event = Auth::user()->events()->onlyTrashed()->find($id);
    
            if (!$event) {
                return response()->json(['message' => 'Event not found in trash'], 404);
            }
    
            $event->restore(); // <-- Khôi phục event
    
            return response()->json(['message' => 'Event restored successfully', 'event' => $event]);
        }
    
        /**
         * [MỚI] - Xóa vĩnh viễn một event.
         * Hàm này có thể được dùng cho Admin hoặc khi người dùng xóa từ thùng rác.
         */
        public function forceDelete($id)
        {
            // Để xóa vĩnh viễn, chúng ta cần tìm trong cả các mục đang hoạt động và đã xóa
            $event = Auth::user()->events()->withTrashed()->find($id);
            
            if (!$event) {
                return response()->json(['message' => 'Event not found'], 404);
            }
    
            // Trước khi xóa vĩnh viễn, bạn phải detach các mối quan hệ trong bảng trung gian
            $event->goals()->detach();
    
            $event->forceDelete(); // <-- XÓA VĨNH VIỄN khỏi DB
    
            return response()->json(['message' => 'Event permanently deleted']);
        }

    public function linkGoal(Request $request, Event $event)
    {
        if ($event->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'goal_id' => 'required|exists:Goals,goal_id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $goal = Goal::find($request->goal_id);
        
        if ($goal->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->goals()->attach($request->goal_id);

        return response()->json(['message' => 'Goal linked to event successfully']);
    }

    public function unlinkGoal(Event $event, $goalId)
    {
        if ($event->user_id !== Auth::user()->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event->goals()->detach($goalId);

        return response()->json(['message' => 'Goal unlinked from event successfully']);
    }
}
