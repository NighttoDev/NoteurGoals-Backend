# 🚀 Deployment Guide: AI Microservice trên Hosting Riêng

## 📋 Tổng quan

Hướng dẫn này sẽ giúp bạn:
1. ✅ Sửa lỗi thư viện Python (red errors)
2. ✅ Deploy AI service lên hosting riêng biệt
3. ✅ Kết nối với database hosting
4. ✅ Train models local và deploy lên production

## 1. Sửa lỗi thư viện (Local Development)

### Bước 1: Setup môi trường Python
```bash
# Tại thư mục ai-microservice
python3 -m venv venv

# Activate virtual environment
# Mac/Linux:
source venv/bin/activate

# Windows:
# venv\Scripts\activate

# Upgrade pip
pip install --upgrade pip

# Cài đặt dependencies
pip install -r requirements.txt
```

### Bước 2: Kiểm tra installation
```bash
# Test các package chính
python -c "import fastapi, sqlalchemy, uvicorn, sklearn; print('✅ All packages installed')"

# Test chạy service
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

## 2. Cấu hình cho Hosting Database

### Bước 1: Tạo Environment Variables
Tạo file `.env` trong thư mục `ai-microservice/`:

```bash
# .env - Production Environment
ENVIRONMENT=production
DEBUG=False

# Database Hosting Configuration
DATABASE_URL=mysql+pymysql://username:password@your-db-host.com:3306/ai_database
DB_HOST=your-db-host.com
DB_PORT=3306
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=ai_database

# Security
SECRET_KEY=your-super-secret-key-here

# CORS Origins (Laravel app URLs)
CORS_ORIGINS=https://your-laravel-app.com,https://api.your-app.com

# Laravel Integration
LARAVEL_API_URL=https://your-laravel-app.com/api
LARAVEL_API_TOKEN=your-laravel-bearer-token

# Logging
LOG_LEVEL=INFO

# Performance
MAX_WORKERS=4
TIMEOUT=30
```

### Bước 2: Setup Database Schema
```sql
-- Chạy trên hosting database
CREATE DATABASE IF NOT EXISTS ai_database;
USE ai_database;

-- Basic tables for AI service
CREATE TABLE user_behavior_metrics (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    session_duration INT DEFAULT 0,
    goals_created_count INT DEFAULT 0,
    goals_completed_count INT DEFAULT 0,
    milestones_achieved_count INT DEFAULT 0,
    avg_goal_completion_time FLOAT DEFAULT 0,
    productivity_score FLOAT DEFAULT 0,
    last_activity_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE goal_complexity_analysis (
    id INT PRIMARY KEY AUTO_INCREMENT,
    goal_id INT NOT NULL,
    complexity_score FLOAT NOT NULL,
    estimated_duration_days INT,
    difficulty_level ENUM('easy', 'medium', 'hard', 'expert') DEFAULT 'medium',
    required_skills JSON,
    success_probability FLOAT DEFAULT 0.5,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ai_predictions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_type ENUM('completion', 'priority', 'breakdown', 'insights') NOT NULL,
    input_data JSON NOT NULL,
    prediction_result JSON NOT NULL,
    confidence_score FLOAT DEFAULT 0,
    goal_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 3. Deploy lên Hosting

### Option A: Direct Upload & Run

#### Bước 1: Chuẩn bị files
```bash
# Tạo package deployment
tar -czf ai-microservice.tar.gz \
    app/ \
    requirements.txt \
    deploy.sh \
    config.production.py \
    docker-compose.production.yml \
    Dockerfile

# Upload lên hosting server
scp ai-microservice.tar.gz user@your-hosting-server:/path/to/deployment/
```

#### Bước 2: Deploy trên hosting server
```bash
# SSH vào hosting server
ssh user@your-hosting-server

# Extract và setup
cd /path/to/deployment/
tar -xzf ai-microservice.tar.gz

# Install Python dependencies
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt

# Set environment variables
export ENVIRONMENT=production
export DATABASE_URL="mysql+pymysql://user:pass@db-host:3306/ai_db"
# ... other env vars

# Run service
./deploy.sh production
```

### Option B: Docker Deployment

#### Bước 1: Build Docker image
```bash
# Local build
docker build -t ai-microservice:latest .

# Save image
docker save ai-microservice:latest | gzip > ai-microservice.tar.gz

# Upload to hosting
scp ai-microservice.tar.gz user@your-hosting-server:/path/to/deployment/
```

#### Bước 2: Deploy với Docker
```bash
# Trên hosting server
ssh user@your-hosting-server

# Load image
cd /path/to/deployment/
gunzip -c ai-microservice.tar.gz | docker load

# Tạo .env file
cat > .env << EOF
ENVIRONMENT=production
DATABASE_URL=mysql+pymysql://user:pass@db-host:3306/ai_db
DB_HOST=your-db-host.com
DB_PORT=3306
DB_USER=your_username
DB_PASSWORD=your_password
DB_NAME=ai_database
SECRET_KEY=your-secret-key
CORS_ORIGINS=https://your-laravel-app.com
LARAVEL_API_URL=https://your-laravel-app.com/api
LARAVEL_API_TOKEN=your-token
EOF

# Run với docker-compose
docker-compose -f docker-compose.production.yml up -d
```

## 4. Train Local & Deploy Models

### Bước 1: Local Training Setup
```bash
# Cài thêm ML dependencies
pip install jupyter notebook matplotlib seaborn

# Tạo training directories
mkdir -p training_data models notebooks

# Export data từ Laravel
curl -X GET "https://your-laravel-app.com/api/export/training-data" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o training_data/raw_data.json
```

### Bước 2: Train Models (Jupyter Notebook)
```python
# notebooks/train_models.ipynb
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier, RandomForestRegressor
from sklearn.model_selection import train_test_split
import joblib
import json

# Load và process data
with open('../training_data/raw_data.json', 'r') as f:
    data = json.load(f)

# Feature engineering
def prepare_features(goals_df):
    features = []
    for goal in goals_df.itertuples():
        feature = {
            'complexity_score': len(goal.description.split()),
            'milestone_count': getattr(goal, 'milestone_count', 0),
            'deadline_days': 30,  # Calculate actual days
            'user_experience': getattr(goal, 'user_goals_count', 1),
            'category_encoded': hash(getattr(goal, 'category', 'general')) % 10,
        }
        features.append(feature)
    return pd.DataFrame(features)

# Train completion prediction model
goals_df = pd.DataFrame(data.get('goals', []))
if len(goals_df) > 0:
    X = prepare_features(goals_df)
    y = goals_df.get('is_completed', [0] * len(goals_df))
    
    if len(X) > 10:  # Minimum data requirement
        X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2)
        
        model = RandomForestClassifier(n_estimators=100, random_state=42)
        model.fit(X_train, y_train)
        
        # Save model
        joblib.dump(model, '../models/goal_completion_model.pkl')
        print("✅ Goal completion model trained and saved")
```

### Bước 3: Upload Models lên Hosting
```bash
# Package models
tar -czf models.tar.gz models/

# Upload
scp models.tar.gz user@your-hosting-server:/path/to/ai-service/

# Extract trên server
ssh user@your-hosting-server "cd /path/to/ai-service && tar -xzf models.tar.gz"

# Restart service để load models mới
ssh user@your-hosting-server "docker-compose restart ai-service"
```

## 5. Kết nối với Laravel Backend

### Bước 1: Update Laravel Service
```php
// app/Services/AIService.php
class AIService
{
    private $baseUrl;
    private $client;

    public function __construct()
    {
        $this->baseUrl = config('services.ai_microservice.url'); // https://your-ai-hosting.com
        $this->client = new GuzzleHttp\Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.ai_microservice.token')
            ]
        ]);
    }

    public function analyzeGoal($goalData)
    {
        try {
            $response = $this->client->post($this->baseUrl . '/api/v1/analysis/goal-breakdown', [
                'json' => $goalData
            ]);
            
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('AI Service Error: ' . $e->getMessage());
            return $this->getFallbackAnalysis($goalData);
        }
    }
}
```

### Bước 2: Update Laravel Config
```php
// config/services.php
'ai_microservice' => [
    'url' => env('AI_MICROSERVICE_URL', 'https://your-ai-hosting.com'),
    'token' => env('AI_MICROSERVICE_TOKEN', 'your-secure-token'),
    'timeout' => env('AI_MICROSERVICE_TIMEOUT', 30),
],
```

## 6. Monitoring & Health Checks

### Health Check Endpoint
```bash
# Test service health
curl https://your-ai-hosting.com/health

# Expected response:
{
    "status": "healthy",
    "database": "connected",
    "models": "loaded",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### Log Monitoring
```bash
# Check logs trên hosting server
tail -f /var/log/ai-service.log

# Docker logs
docker logs ai-goal-service -f
```

## 7. Troubleshooting

### Common Issues:

**1. Database Connection Failed**
```bash
# Test database connection
python -c "from app.core.database import engine; print(engine.execute('SELECT 1').scalar())"
```

**2. Models Not Loading**
```bash
# Check model files
ls -la models/
# Expected: goal_completion_model.pkl, priority_ranking_model.pkl
```

**3. CORS Issues**
```bash
# Update CORS_ORIGINS trong .env
CORS_ORIGINS=https://your-laravel-domain.com,https://your-frontend-domain.com
```

**4. Performance Issues**
```bash
# Increase workers
export MAX_WORKERS=4

# Monitor resource usage
htop
docker stats
```

## 8. Security Checklist

- [ ] ✅ Secret keys khác nhau cho production
- [ ] ✅ Database credentials secure
- [ ] ✅ CORS origins restricted
- [ ] ✅ HTTPS enabled
- [ ] ✅ API authentication implemented
- [ ] ✅ Regular security updates

## 9. Backup & Recovery

### Model Backup
```bash
# Backup models định kỳ
tar -czf models_backup_$(date +%Y%m%d).tar.gz models/
aws s3 cp models_backup_*.tar.gz s3://your-backup-bucket/
```

### Database Backup
```bash
# Backup AI database
mysqldump -h your-db-host -u username -p ai_database > ai_db_backup.sql
```

Với setup này, bạn có thể:

✅ **Chạy AI service trên hosting riêng biệt**  
✅ **Sử dụng database hosting**  
✅ **Train models local với data thật**  
✅ **Deploy trained models lên production**  
✅ **Monitor và scale service**  
✅ **Integrate seamlessly với Laravel backend** 