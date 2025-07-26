# Production Configuration
import os

class ProductionConfig:
    # Environment
    DEBUG = False
    ENVIRONMENT = "production"
    
    # External Database Configuration
    # Replace these with your actual hosting database credentials
    DATABASE_URL = os.getenv(
        "DATABASE_URL", 
        "mysql+pymysql://username:password@your-database-host:3306/database_name"
    )
    DB_HOST = os.getenv("DB_HOST", "your-database-host.com")
    DB_PORT = int(os.getenv("DB_PORT", "3306"))
    DB_USER = os.getenv("DB_USER", "your_db_username")
    DB_PASSWORD = os.getenv("DB_PASSWORD", "your_db_password")
    DB_NAME = os.getenv("DB_NAME", "ai_microservice_db")
    
    # API Configuration
    API_V1_PREFIX = "/api/v1"
    PROJECT_NAME = "AI Goal Analysis Service"
    VERSION = "1.0.0"
    
    # Security
    SECRET_KEY = os.getenv("SECRET_KEY", "your-production-secret-key-here")
    CORS_ORIGINS = [
        "https://your-laravel-domain.com",
        "https://your-frontend-domain.com"
    ]
    
    # External Services
    LARAVEL_API_URL = os.getenv("LARAVEL_API_URL", "https://your-laravel-api.com/api")
    LARAVEL_API_TOKEN = os.getenv("LARAVEL_API_TOKEN", "your-laravel-api-token")
    
    # Logging
    LOG_LEVEL = "INFO"
    LOG_FILE = "/var/log/ai-service.log"
    
    # Performance
    MAX_WORKERS = 4
    TIMEOUT = 30
    
    # Machine Learning
    MODEL_PATH = "/app/models"
    TRAINING_DATA_PATH = "/app/training_data"
    MODEL_VERSION = "1.0.0"
    
    # Health Check
    HEALTH_CHECK_INTERVAL = 60 