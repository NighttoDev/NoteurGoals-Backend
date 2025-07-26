# 🤖 Machine Learning Training & Deployment Guide

## 1. Local Training Setup

### Chuẩn bị môi trường local
```bash
# Clone và setup project
git clone <your-ai-microservice-repo>
cd ai-microservice

# Tạo virtual environment
python3 -m venv venv
source venv/bin/activate  # Linux/Mac
# hoặc venv\Scripts\activate  # Windows

# Cài đặt dependencies
pip install -r requirements.txt

# Cài thêm dependencies cho training
pip install jupyter notebook matplotlib seaborn plotly
```

### Chuẩn bị data training
```bash
# Tạo thư mục training data
mkdir -p training_data
mkdir -p models
mkdir -p notebooks

# Export data từ Laravel backend
curl -X GET "http://your-laravel-app.com/api/export/training-data" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -o training_data/raw_data.json
```

## 2. Data Processing & Training

### Tạo training notebook
```python
# notebooks/train_models.ipynb
import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestRegressor, RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import accuracy_score, mean_squared_error
import joblib
import json

# Load data
with open('../training_data/raw_data.json', 'r') as f:
    data = json.load(f)

# Process data
df_goals = pd.DataFrame(data['goals'])
df_users = pd.DataFrame(data['users'])
df_milestones = pd.DataFrame(data['milestones'])

# Feature engineering cho Goal Completion Prediction
def prepare_goal_features(df):
    features = []
    for goal in df.itertuples():
        feature = {
            'complexity_score': len(goal.description.split()),
            'milestone_count': goal.milestone_count,
            'deadline_days': (pd.to_datetime(goal.deadline) - pd.to_datetime(goal.created_at)).days,
            'user_experience': goal.user_goals_count,
            'category_encoded': hash(goal.category) % 10,
        }
        features.append(feature)
    return pd.DataFrame(features)

# Training Goal Completion Model
X = prepare_goal_features(df_goals)
y = df_goals['is_completed']

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Train model
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Evaluate
predictions = model.predict(X_test)
accuracy = accuracy_score(y_test, predictions)
print(f"Goal Completion Accuracy: {accuracy:.4f}")

# Save model
joblib.dump(model, '../models/goal_completion_model.pkl')
```

### Training Priority Model
```python
# Priority Ranking Model
def prepare_priority_features(df):
    features = []
    for goal in df.itertuples():
        feature = {
            'days_to_deadline': (pd.to_datetime(goal.deadline) - pd.datetime.now()).days,
            'progress_percentage': goal.progress_percentage,
            'milestone_completion_rate': goal.completed_milestones / max(goal.total_milestones, 1),
            'user_activity_score': goal.user_last_activity_days,
            'goal_importance': goal.importance_level,
        }
        features.append(feature)
    return pd.DataFrame(features)

# Train Priority Model
X_priority = prepare_priority_features(df_goals)
y_priority = df_goals['priority_score']  # Manual priority scores

priority_model = RandomForestRegressor(n_estimators=100, random_state=42)
X_train_p, X_test_p, y_train_p, y_test_p = train_test_split(X_priority, y_priority, test_size=0.2)
priority_model.fit(X_train_p, y_train_p)

# Save priority model
joblib.dump(priority_model, '../models/priority_ranking_model.pkl')
```

## 3. Model Validation & Testing

### Local testing
```python
# test_models.py
import joblib
import pandas as pd

# Load models
completion_model = joblib.load('models/goal_completion_model.pkl')
priority_model = joblib.load('models/priority_ranking_model.pkl')

# Test với data mẫu
test_goal = {
    'complexity_score': 25,
    'milestone_count': 5,
    'deadline_days': 30,
    'user_experience': 10,
    'category_encoded': 3
}

# Predict completion probability
completion_prob = completion_model.predict_proba([list(test_goal.values())])[0][1]
print(f"Completion Probability: {completion_prob:.4f}")

# Test priority ranking
priority_score = priority_model.predict([list(test_goal.values())])[0]
print(f"Priority Score: {priority_score:.4f}")
```

## 4. Deploy Models lên Hosting

### 4.1 Packaging Models
```bash
# Tạo model package
tar -czf models.tar.gz models/
```

### 4.2 Upload lên hosting
```bash
# Upload models lên server
scp models.tar.gz user@your-hosting-server:/path/to/ai-service/
ssh user@your-hosting-server "cd /path/to/ai-service && tar -xzf models.tar.gz"
```

### 4.3 Update Production Config
```python
# config.production.py
MODEL_PATH = "/app/models"
MODELS = {
    'goal_completion': 'goal_completion_model.pkl',
    'priority_ranking': 'priority_ranking_model.pkl',
    'user_insights': 'user_insights_model.pkl',
    'goal_breakdown': 'goal_breakdown_model.pkl'
}
```

### 4.4 Update Service để sử dụng trained models
```python
# app/services/ml_predictor.py
import joblib
import os
from app.core.config import get_settings

class MLPredictor:
    def __init__(self):
        self.settings = get_settings()
        self.models = {}
        self.load_models()
    
    def load_models(self):
        """Load trained ML models"""
        try:
            model_path = self.settings.model_path
            self.models['completion'] = joblib.load(f"{model_path}/goal_completion_model.pkl")
            self.models['priority'] = joblib.load(f"{model_path}/priority_ranking_model.pkl")
            print("✅ ML Models loaded successfully")
        except Exception as e:
            print(f"⚠️ Could not load ML models: {e}")
            self.models = {}
    
    def predict_completion(self, goal_features):
        """Predict goal completion probability"""
        if 'completion' in self.models:
            return self.models['completion'].predict_proba([goal_features])[0][1]
        else:
            # Fallback to rule-based
            return self._rule_based_completion(goal_features)
    
    def predict_priority(self, goal_features):
        """Predict goal priority score"""
        if 'priority' in self.models:
            return self.models['priority'].predict([goal_features])[0]
        else:
            # Fallback to rule-based
            return self._rule_based_priority(goal_features)
```

## 5. Continuous Training Pipeline

### 5.1 Data Collection Script
```python
# scripts/collect_training_data.py
import requests
import json
from datetime import datetime

def collect_training_data():
    """Collect new training data from Laravel API"""
    
    # Fetch new goals, completions, user behavior
    goals_response = requests.get(f"{LARAVEL_API_URL}/goals/training-data")
    users_response = requests.get(f"{LARAVEL_API_URL}/users/behavior-data")
    
    training_data = {
        'timestamp': datetime.now().isoformat(),
        'goals': goals_response.json(),
        'users': users_response.json()
    }
    
    # Save to training data
    with open(f'training_data/data_{datetime.now().strftime("%Y%m%d")}.json', 'w') as f:
        json.dump(training_data, f)

if __name__ == "__main__":
    collect_training_data()
```

### 5.2 Automated Retraining
```python
# scripts/retrain_models.py
def retrain_models():
    """Retrain models với data mới"""
    
    # Load all training data
    data_files = glob.glob('training_data/*.json')
    all_data = []
    
    for file in data_files:
        with open(file, 'r') as f:
            all_data.extend(json.load(f)['goals'])
    
    # Retrain models
    df = pd.DataFrame(all_data)
    # ... training logic ...
    
    # Save new models với timestamp
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    joblib.dump(model, f'models/goal_completion_model_{timestamp}.pkl')
    
    # Update production model
    shutil.copy(
        f'models/goal_completion_model_{timestamp}.pkl',
        'models/goal_completion_model.pkl'
    )
```

## 6. Deployment Checklist

### Local Training:
- [ ] Data collected và processed
- [ ] Models trained và validated
- [ ] Performance metrics documented
- [ ] Models saved to `/models` directory

### Production Deployment:
- [ ] Models uploaded to hosting server
- [ ] Environment variables configured
- [ ] Health checks passing
- [ ] Fallback logic tested
- [ ] Monitoring setup

### Monitoring:
- [ ] Model performance tracking
- [ ] API response times
- [ ] Prediction accuracy monitoring
- [ ] Auto-retraining schedule

## 7. Database Schema cho ML

```sql
-- Thêm vào existing database
CREATE TABLE ml_model_versions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    version VARCHAR(50) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    performance_metrics JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT FALSE
);

CREATE TABLE ml_predictions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    model_name VARCHAR(100) NOT NULL,
    input_features JSON NOT NULL,
    prediction_result JSON NOT NULL,
    confidence_score FLOAT,
    goal_id INT,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (goal_id) REFERENCES goals(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE ml_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    prediction_id INT NOT NULL,
    actual_outcome JSON,
    feedback_score INT CHECK (feedback_score BETWEEN 1 AND 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prediction_id) REFERENCES ml_predictions(id)
);
```

Với setup này, bạn có thể:
1. ✅ Train models locally với data thật
2. ✅ Deploy trained models lên hosting
3. ✅ Sử dụng external database hosting
4. ✅ Monitor và improve models theo thời gian
5. ✅ Fallback về rule-based khi cần thiết 