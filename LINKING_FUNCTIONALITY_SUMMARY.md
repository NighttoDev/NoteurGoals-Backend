# Linking Functionality Summary

## Tổng quan
Dự án NoteurGoals có hệ thống linking phong phú cho phép liên kết các entity với nhau thông qua các bảng trung gian (pivot tables). Đây là tổng hợp tất cả linking functionality đã được implement.

## Các Model có Linking Functionality

### 1. Note Model
**Linking Tables:**
- `NoteGoalLinks` - Liên kết Note ↔ Goal
- `NoteMilestoneLinks` - Liên kết Note ↔ Milestone  
- `FileNoteLinks` - Liên kết File ↔ Note

**Methods:**
```php
// Goal linking
$note->linkGoal($goalId)
$note->unlinkGoal($goalId)
$note->isLinkedToGoal($goalId)

// Milestone linking  
$note->linkMilestone($milestoneId)
$note->unlinkMilestone($milestoneId)
$note->isLinkedToMilestone($milestoneId)

// Helper methods
$note->hasGoalLinks()
$note->hasMilestoneLinks()
$note->hasAttachments()
$note->getGoalLinksCount()
$note->getMilestoneLinksCount()
```

**API Routes:**
```
POST   /api/notes/{note}/goals           - Link note to goal
DELETE /api/notes/{note}/goals/{goalId} - Unlink note from goal
POST   /api/notes/{note}/milestones     - Link note to milestone  
DELETE /api/notes/{note}/milestones/{milestoneId} - Unlink note from milestone
```

### 2. File Model
**Linking Tables:**
- `FileGoalLinks` - Liên kết File ↔ Goal
- `FileNoteLinks` - Liên kết File ↔ Note

**Methods:**
```php
// Goal linking
$file->linkGoal($goalId)
$file->unlinkGoal($goalId)
$file->isLinkedToGoal($goalId)

// Note linking
$file->linkNote($noteId)
$file->unlinkNote($noteId)
$file->isLinkedToNote($noteId)

// Helper methods
$file->hasGoalLinks()
$file->hasNoteLinks()
$file->hasAnyLinks()
$file->getGoalLinksCount()
$file->getNoteLinksCount()

// File utility methods
$file->getFileSize()           // Human readable size
$file->getFileExtension()      // File extension
$file->isImage()              // Check if image
$file->isDocument()           // Check if document
```

**API Routes:**
```
GET    /api/files/{file}/with-links       - Get file with all links
GET    /api/files/{file}/download         - Download file
POST   /api/files/{file}/goals           - Link file to goal
DELETE /api/files/{file}/goals/{goalId}  - Unlink file from goal
POST   /api/files/{file}/notes           - Link file to note
DELETE /api/files/{file}/notes/{noteId}  - Unlink file from note
```

### 3. Event Model
**Linking Tables:**
- `EventGoalLinks` - Liên kết Event ↔ Goal

**Methods:**
```php
// Goal linking
$event->linkGoal($goalId)
$event->unlinkGoal($goalId)
$event->isLinkedToGoal($goalId)

// Helper methods
$event->hasGoalLinks()
$event->getGoalLinksCount()

// Event status methods
$event->isPast()              // Check if event is past
$event->isToday()             // Check if event is today
$event->isFuture()            // Check if event is future
$event->isUpcoming()          // Check if event is upcoming (within 7 days)
$event->getStatusText()       // Get status as text
$event->getTimeUntilEvent()   // Get time until event (Vietnamese)
```

**API Routes:**
```
POST   /api/events/{event}/goals          - Link event to goal
DELETE /api/events/{event}/goals/{goalId} - Unlink event from goal
```

**Query Scopes:**
```php
Event::past()           // Get past events
Event::today()          // Get today's events  
Event::upcoming()       // Get upcoming events
Event::thisWeek()       // Get this week's events
```

### 4. AISuggestion Model
**Linking Tables:**
- `AISuggestionGoalLinks` - Liên kết AISuggestion ↔ Goal

**Methods:**
```php
// Goal linking
$suggestion->linkGoal($goalId)
$suggestion->unlinkGoal($goalId)
$suggestion->isLinkedToGoal($goalId)

// Helper methods
$suggestion->hasGoalLinks()
$suggestion->getGoalLinksCount()

// Suggestion type helpers
$suggestion->isGoalBreakdown()
$suggestion->isPriority()
$suggestion->isCompletionForecast()
$suggestion->getTypeText()       // Vietnamese text for type

// Read status methods
$suggestion->markAsRead()
$suggestion->markAsUnread()

// Content helpers
$suggestion->getShortContent($length = 100)
```

**API Routes:**
```
GET    /api/ai-suggestions/{suggestion}                   - Get single suggestion
GET    /api/ai-suggestions/{suggestion}/with-links       - Get suggestion with links
GET    /api/ai-suggestions/type/{type}                   - Get suggestions by type
GET    /api/ai-suggestions/unread/count                  - Get unread count
POST   /api/ai-suggestions/{suggestion}/read             - Mark as read
POST   /api/ai-suggestions/{suggestion}/unread           - Mark as unread
POST   /api/ai-suggestions/read-all                      - Mark all as read
POST   /api/ai-suggestions/{suggestion}/goals            - Link suggestion to goal
DELETE /api/ai-suggestions/{suggestion}/goals/{goalId}   - Unlink suggestion from goal
```

**Query Scopes:**
```php
AISuggestion::unread()                    // Get unread suggestions
AISuggestion::read()                      // Get read suggestions
AISuggestion::byType($type)               // Get by suggestion type
AISuggestion::forUser($userId)            // Get for specific user
```

## Linking Tables trong Database

### Cấu trúc chung
Tất cả linking tables đều có cấu trúc tương tự:
```sql
CREATE TABLE `{Entity1}{Entity2}Links` (
  `link_id` int(11) NOT NULL AUTO_INCREMENT,
  `{entity1}_id` int(11) NOT NULL,
  `{entity2}_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`link_id`),
  FOREIGN KEY (`{entity1}_id`) REFERENCES `{Entity1s}` (`{entity1}_id`) ON DELETE CASCADE,
  FOREIGN KEY (`{entity2}_id`) REFERENCES `{Entity2s}` (`{entity2}_id`) ON DELETE CASCADE
);
```

### Danh sách tất cả Linking Tables
1. **NoteGoalLinks** - Note ↔ Goal
2. **NoteMilestoneLinks** - Note ↔ Milestone
3. **FileGoalLinks** - File ↔ Goal
4. **FileNoteLinks** - File ↔ Note
5. **EventGoalLinks** - Event ↔ Goal
6. **AISuggestionGoalLinks** - AISuggestion ↔ Goal
7. **CourseGoalLinks** - Course ↔ Goal (future feature)

## Mô hình quan hệ

```
Goal (central entity)
├── Notes (nhiều-nhiều qua NoteGoalLinks)
├── Files (nhiều-nhiều qua FileGoalLinks)  
├── Events (nhiều-nhiều qua EventGoalLinks)
├── AISuggestions (nhiều-nhiều qua AISuggestionGoalLinks)
└── Milestones (một-nhiều)

Note
├── Goals (nhiều-nhiều qua NoteGoalLinks)
├── Milestones (nhiều-nhiều qua NoteMilestoneLinks)
└── Files (nhiều-nhiều qua FileNoteLinks)

File
├── Goals (nhiều-nhiều qua FileGoalLinks)
└── Notes (nhiều-nhiều qua FileNoteLinks)
```

## Các tính năng đặc biệt

### 1. Authorization
Tất cả linking methods đều kiểm tra quyền sở hữu:
- Chỉ owner của entity mới có thể link/unlink
- Chỉ có thể link với entity thuộc cùng user

### 2. Duplicate Prevention
- Các linking methods kiểm tra tồn tại trước khi tạo link mới
- Trả về false nếu link đã tồn tại

### 3. Cascade Deletion
- Tất cả linking tables có CASCADE DELETE
- Khi xóa entity chính, tất cả links sẽ bị xóa theo

### 4. Helper Methods phong phú
- Check existence: `hasXxxLinks()`, `isLinkedToXxx()`
- Count links: `getXxxLinksCount()`
- Business logic helpers cho từng entity type

### 5. Query Scopes
- Filtering by status, type, time
- Easy to use in controllers

## Cách sử dụng

### Trong Controller
```php
// Link entities
$note->linkGoal($goalId);
$file->linkNote($noteId);  
$event->linkGoal($goalId);

// Check links
if ($note->isLinkedToGoal($goalId)) {
    // Do something
}

// Get counts
$goalLinksCount = $file->getGoalLinksCount();
```

### Trong Eloquent Relationships
```php
// Load với relationships
$note = Note::with(['goals', 'milestones'])->find($id);
$file = File::with(['goals', 'notes'])->find($id);

// Access linked entities
foreach ($note->goals as $goal) {
    echo $goal->title;
}
```

### Filtering và Querying
```php
// Get events với goals
$events = Event::whereHas('goals')->get();

// Get files không có links
$orphanFiles = File::whereDoesntHave('goals')
                  ->whereDoesntHave('notes')
                  ->get();

// Get suggestions by type
$prioritySuggestions = AISuggestion::byType('priority')
                                  ->unread()
                                  ->get();
```

## Test Script
Có thể sử dụng `test_linking_functionality.php` để test tất cả linking functionality:

```bash
php test_linking_functionality.php
```

Script sẽ test:
- Note ↔ Goal linking
- File ↔ Goal/Note linking
- Event ↔ Goal linking  
- AISuggestion functionality và Goal linking
- All helper methods và API endpoints

## Kết luận
Hệ thống linking đã được implement hoàn chỉnh với:
- ✅ 6 linking tables chính
- ✅ 4 models với linking functionality
- ✅ 20+ API endpoints cho linking
- ✅ 40+ helper methods
- ✅ Full authorization và validation
- ✅ Comprehensive test script

Hệ thống này cho phép users tạo ra các mối quan hệ phức tạp giữa notes, files, events, và AI suggestions với goals, tạo nên một hệ thống quản lý mục tiêu toàn diện và linh hoạt. 