# AI Microservice Structure

```
ai-microservice/
├── app/
│   ├── __init__.py
│   ├── main.py                 # FastAPI app
│   ├── config.py              # Cấu hình
│   ├── database.py            # Database connections
│   ├── models/
│   │   ├── __init__.py
│   │   ├── schemas.py         # Pydantic models
│   │   ├── ml_models.py       # ML model definitions
│   │   └── database_models.py # SQLAlchemy models
│   ├── services/
│   │   ├── __init__.py
│   │   ├── data_processor.py  # Xử lý dữ liệu
│   │   ├── goal_analyzer.py   # Phân tích mục tiêu
│   │   ├── predictor.py       # Dự đoán
│   │   └── recommender.py     # Gợi ý
│   ├── api/
│   │   ├── __init__.py
│   │   ├── endpoints/
│   │   │   ├── __init__.py
│   │   │   ├── analysis.py    # API phân tích
│   │   │   ├── predictions.py # API dự đoán
│   │   │   └── recommendations.py # API gợi ý
│   │   └── dependencies.py
│   ├── core/
│   │   ├── __init__.py
│   │   ├── security.py        # Authentication
│   │   └── exceptions.py      # Custom exceptions
│   └── utils/
│       ├── __init__.py
│       ├── data_validation.py
│       └── metrics.py
├── ml_models/
│   ├── trained_models/        # Saved models
│   ├── training_scripts/      # Training code
│   └── model_configs/         # Model configurations
├── tests/
├── requirements.txt
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## Core Technologies:
- **FastAPI**: REST API framework
- **Scikit-learn**: ML algorithms
- **Pandas/Numpy**: Data processing
- **SQLAlchemy**: Database ORM
- **Redis**: Caching
- **Celery**: Background tasks
- **TensorFlow/PyTorch**: Deep learning (tùy chọn) 