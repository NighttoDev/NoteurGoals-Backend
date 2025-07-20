# AI Microservice cho Goal Management System

## 🎯 Tổng quan

AI Microservice cung cấp các tính năng phân tích thông minh cho Goal Management System:

- **Goal Breakdown**: Phân tích và chia nhỏ mục tiêu
- **Priority Suggestions**: Gợi ý ưu tiên mục tiêu  
- **Completion Forecast**: Dự đoán khả năng hoàn thành
- **User Insights**: Phân tích patterns và đưa ra insights cá nhân

## 🚀 Quick Start

### 1. Cài đặt Dependencies

```bash
# Clone repository (nếu chưa có)
cd ai-microservice

# Chạy setup script
python setup.py
```

### 2. Manual Setup (nếu setup script không hoạt động)

```bash
# Tạo virtual environment
python -m venv venv

# Activate virtual environment
# Linux/Mac:
source venv/bin/activate
# Windows:
venv\Scripts\activate

# Install dependencies
pip install -r requirements.txt

# Tạo directories cần thiết
mkdir -p ml_models/trained_models
mkdir -p training_data
mkdir -p logs
```

### 3. Cấu hình Database

```bash
# Copy và edit file environment
cp .env.example .env
# Hoặc tạo .env file với nội dung:

DATABASE_URL=mysql://root:password@localhost:3306/GoalManagementSystem
REDIS_URL=redis://localhost:6379
LOG_LEVEL=INFO
```

### 4. Chạy Service

```bash
# Activate virtual environment
source venv/bin/activate  # Linux/Mac
# venv\Scripts\activate   # Windows

# Chạy development server
uvicorn app.main:app --reload --host 0.0.0.0 --port 8001

# Hoặc chạy production
uvicorn app.main:app --host 0.0.0.0 --port 8001 --workers 4
```

### 5. Test Service

```bash
# Health check
curl http://localhost:8001/health

# API documentation
open http://localhost:8001/docs
```

## 🐳 Docker Setup

### Build và chạy với Docker

```bash
# Build image
docker build -t ai-microservice .

# Run container
docker run -p 8001:8000 \
  -e DATABASE_URL=mysql://root:password@host.docker.internal:3306/GoalManagementSystem \
  ai-microservice
```

### Sử dụng Docker Compose

```bash
# Từ thư mục root project
docker-compose -f docker-compose.ai.yml up -d
```

## 📊 API Endpoints

### Health Check
- `GET /health` - Basic health check
- `GET /api/v1/health/detailed` - Detailed health check

### Analysis Endpoints
- `POST /api/v1/analyze/goal-breakdown` - Phân tích breakdown mục tiêu
- `POST /api/v1/analyze/priority-suggestions` - Gợi ý ưu tiên
- `POST /api/v1/analyze/completion-forecast` - Dự báo hoàn thành
- `GET /api/v1/analyze/user-insights/{user_id}` - Insights người dùng

### Batch Processing
- `POST /api/v1/analyze/batch-analyze-users` - Phân tích batch users

## 📝 Example Usage

### Phân tích Goal Breakdown

```bash
curl -X POST "http://localhost:8001/api/v1/analyze/goal-breakdown" \
  -H "Content-Type: application/json" \
  -d '{
    "goal_id": 1,
    "title": "Học Python Programming",
    "description": "Hoàn thành khóa học Python từ cơ bản đến nâng cao",
    "start_date": "2024-01-01T00:00:00Z",
    "end_date": "2024-03-01T00:00:00Z",
    "user_context": {}
  }'
```

### Gợi ý Priority

```bash
curl -X POST "http://localhost:8001/api/v1/analyze/priority-suggestions" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "goals": [...],
    "milestones": [...],
    "behavior_metrics": {...}
  }'
```

## 🔧 Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `DATABASE_URL` | MySQL connection string | `mysql://root:password@localhost:3306/GoalManagementSystem` |
| `REDIS_URL` | Redis connection string | `redis://localhost:6379` |
| `LOG_LEVEL` | Logging level | `INFO` |
| `ML_MODEL_PATH` | Path to ML models | `/app/ml_models/trained_models` |
| `API_TIMEOUT` | API timeout in seconds | `30` |

### Database Tables

Service sẽ tự động tạo các bảng AI cần thiết:
- `UserBehaviorMetrics` - Metrics hành vi người dùng
- `GoalComplexityAnalysis` - Phân tích độ phức tạp mục tiêu
- `AITrainingDataset` - Dataset để train AI
- `AIModelMetrics` - Metrics performance của models
- `AISuggestionFeedback` - Feedback từ users

## 🧠 AI Models

### Current Implementation
- **Rule-based Analysis**: Logic dựa trên rules cho MVP
- **Statistical Analysis**: Phân tích thống kê cơ bản
- **Pattern Recognition**: Nhận diện patterns từ user behavior

### Future ML Models
- **Goal Complexity Prediction**: Random Forest Regressor
- **Completion Probability**: Gradient Boosting Classifier  
- **Priority Recommendation**: Collaborative Filtering
- **User Clustering**: K-means clustering

## 🔍 Monitoring

### Metrics Endpoints
- `GET /metrics` - Prometheus metrics
- `GET /api/v1/health/detailed` - Service health với component status

### Logging
Logs được structured với JSON format, levels:
- `INFO`: General operation info
- `ERROR`: Errors và exceptions
- `DEBUG`: Detailed debug information

## 🚨 Troubleshooting

### Common Issues

1. **Import errors**: Đảm bảo tất cả __init__.py files tồn tại
2. **Database connection**: Check DATABASE_URL trong .env
3. **Port conflicts**: Đổi port từ 8001 sang port khác
4. **Dependencies**: Reinstall với `pip install -r requirements.txt`

### Debug Commands

```bash
# Check service logs
docker logs ai-microservice

# Connect to database
mysql -h localhost -u root -p GoalManagementSystem

# Test Redis connection
redis-cli ping
```

## 📚 Development

### Project Structure
```
ai-microservice/
├── app/
│   ├── main.py              # FastAPI app
│   ├── core/                # Core configs và database
│   ├── api/endpoints/       # API endpoints
│   ├── services/            # Business logic services
│   └── models/              # Data models
├── ml_models/               # Trained ML models
├── requirements.txt         # Python dependencies
├── Dockerfile              # Docker configuration
└── setup.py                # Setup script
```

### Adding New Features

1. Tạo endpoint mới trong `app/api/endpoints/`
2. Implement business logic trong `app/services/`
3. Update database models nếu cần
4. Add tests trong `tests/`
5. Update documentation

## 🤝 Integration với Laravel

### Laravel Service Class
```php
// Đã tạo sẵn trong src/app/Services/AIService.php
$aiService = app(\App\Services\AIService::class);
$analysis = $aiService->analyzeGoalBreakdown($goal);
```

### API Routes
```php
// Đã thêm vào src/routes/api.php
Route::post('/ai-analysis/goals/{goal}', [AISuggestionController::class, 'analyzeGoal']);
Route::post('/ai-analysis/priority-suggestions', [AISuggestionController::class, 'getPrioritySuggestions']);
```

## 📈 Performance

### Optimization Tips
- Enable Redis caching cho frequent requests
- Use batch processing cho multiple users
- Implement rate limiting
- Monitor memory usage cho ML models

### Scaling
- Horizontal scaling với multiple containers
- Load balancing với Nginx
- Database read replicas
- Model serving với dedicated servers

## 🔐 Security

- API key authentication (optional)
- Input validation với Pydantic
- Rate limiting protection
- Database query optimization để tránh injection

---

**Happy coding! 🚀**

Nếu có issues, hãy check troubleshooting section hoặc tạo issue trong repository. 