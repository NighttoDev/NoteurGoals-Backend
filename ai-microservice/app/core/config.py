import os
from typing import List, Optional
from pydantic_settings import BaseSettings
from functools import lru_cache

class Settings(BaseSettings):
    # Environment
    debug: bool = os.getenv("DEBUG", "True").lower() == "true"
    environment: str = os.getenv("ENVIRONMENT", "development")
    
    # Database Configuration
    database_url: str = os.getenv(
        "DATABASE_URL", 
        "mysql+pymysql://root:password@localhost:3306/ai_analysis"
    )
    db_host: str = os.getenv("DB_HOST", "localhost")
    db_port: int = int(os.getenv("DB_PORT", "3306"))
    db_user: str = os.getenv("DB_USER", "root")
    db_password: str = os.getenv("DB_PASSWORD", "password")
    db_name: str = os.getenv("DB_NAME", "ai_analysis")
    
    # API Configuration
    api_v1_prefix: str = "/api/v1"
    project_name: str = "AI Goal Analysis Service"
    version: str = "1.0.0"
    
    # Security
    secret_key: str = os.getenv("SECRET_KEY", "your-secret-key-here")
    
    # External Services
    laravel_api_url: str = os.getenv("LARAVEL_API_URL", "http://localhost:8000/api")
    laravel_api_token: Optional[str] = os.getenv("LARAVEL_API_TOKEN")
    
    # Logging
    log_level: str = os.getenv("LOG_LEVEL", "DEBUG")
    log_file: Optional[str] = os.getenv("LOG_FILE")
    
    # Performance
    max_workers: int = int(os.getenv("MAX_WORKERS", "1"))
    timeout: int = int(os.getenv("TIMEOUT", "30"))
    
    # Machine Learning
    model_path: str = os.getenv("MODEL_PATH", "./models")
    training_data_path: str = os.getenv("TRAINING_DATA_PATH", "./training_data")
    model_version: str = os.getenv("MODEL_VERSION", "1.0.0")
    
    model_config = {
        "env_file": ".env",
        "case_sensitive": False,
        "protected_namespaces": (),  # Disable protected namespace warnings
        "extra": "ignore"  # Ignore extra fields in .env
    }

    def get_database_url(self) -> str:
        """Get database URL based on environment"""
        if self.environment == "production":
            return self.database_url
        else:
            return f"mysql+pymysql://{self.db_user}:{self.db_password}@{self.db_host}:{self.db_port}/{self.db_name}"

    def get_cors_origins(self) -> List[str]:
        """Get CORS origins based on environment"""
        if self.environment == "production":
            # Production CORS origins from environment
            cors_str = os.getenv("CORS_ORIGINS", "")
            if cors_str:
                return [origin.strip() for origin in cors_str.split(",")]
        
        # Default development CORS origins
        return [
            "http://localhost:3000",
            "http://localhost:8000",
            "http://127.0.0.1:3000",
            "http://127.0.0.1:8000",
        ]

@lru_cache()
def get_settings() -> Settings:
    return Settings() 