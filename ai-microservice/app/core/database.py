from sqlalchemy import create_engine, Column, Integer, String, Text, Float, DateTime, Boolean, JSON
from sqlalchemy.ext.declarative import declarative_base
from sqlalchemy.orm import sessionmaker, Session
from sqlalchemy.sql import func
from app.core.config import get_settings
import structlog

logger = structlog.get_logger()

# Get settings
settings = get_settings()

# Create SQLAlchemy engine - AI microservice database only
engine = create_engine(
    settings.get_database_url(),
    echo=settings.debug,
    pool_size=10,
    max_overflow=20
)

# Create SessionLocal class
SessionLocal = sessionmaker(autocommit=False, autoflush=False, bind=engine)

# Create Base class
Base = declarative_base()

# Dependency to get DB session
def get_db() -> Session:
    """
    Database session generator for AI microservice
    """
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()

# AI-specific Analytics Models (No conflict with Laravel)
class UserBehaviorMetrics(Base):
    __tablename__ = "user_behavior_metrics"
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, nullable=False)  # Reference to Laravel user ID
    session_duration = Column(Integer, default=0)
    goals_created_count = Column(Integer, default=0)
    goals_completed_count = Column(Integer, default=0)
    milestones_achieved_count = Column(Integer, default=0)
    avg_goal_completion_time = Column(Float, default=0)
    productivity_score = Column(Float, default=0)
    last_activity_date = Column(DateTime)
    created_at = Column(DateTime, default=func.now())
    updated_at = Column(DateTime, default=func.now(), onupdate=func.now())

class GoalAnalysisResult(Base):
    __tablename__ = "goal_analysis_results"
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    goal_id = Column(Integer, nullable=False)  # Reference to Laravel goal ID
    user_id = Column(Integer, nullable=False)  # Reference to Laravel user ID
    analysis_type = Column(String(50), nullable=False)  # breakdown, priority, forecast, insights
    input_data = Column(JSON, nullable=False)
    results = Column(JSON, nullable=False)
    confidence_score = Column(Float, default=0.0)
    processing_time_ms = Column(Integer, default=0)
    created_at = Column(DateTime, default=func.now())

class AIModelMetrics(Base):
    __tablename__ = "ai_model_metrics"
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    model_name = Column(String(100), nullable=False)
    model_type = Column(String(50))  # completion, priority, breakdown, insights
    accuracy = Column(Float, default=0)
    precision_score = Column(Float, default=0)
    recall_score = Column(Float, default=0)
    f1_score = Column(Float, default=0)
    last_trained = Column(DateTime, default=func.now())
    training_data_size = Column(Integer, default=0)

class AIRequestLog(Base):
    __tablename__ = "ai_request_logs"
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    endpoint = Column(String(200), nullable=False)
    user_id = Column(Integer)
    request_data = Column(JSON)
    response_data = Column(JSON)
    processing_time_ms = Column(Integer)
    status_code = Column(Integer)
    error_message = Column(Text)
    created_at = Column(DateTime, default=func.now())

class UserInsights(Base):
    __tablename__ = "user_insights"
    
    id = Column(Integer, primary_key=True, autoincrement=True)
    user_id = Column(Integer, nullable=False)
    insight_type = Column(String(50), nullable=False)  # productivity, patterns, recommendations
    insights = Column(JSON, nullable=False)
    confidence_score = Column(Float, default=0.0)
    generated_at = Column(DateTime, default=func.now())
    is_active = Column(Boolean, default=True)

# Utility functions
async def check_database_connection():
    """
    Test AI microservice database connection
    """
    try:
        db = SessionLocal()
        db.execute("SELECT 1")
        db.close()
        logger.info("✅ AI Database connection successful")
        return True
    except Exception as e:
        logger.error(f"❌ AI Database connection failed: {e}")
        return False

def create_tables():
    """
    Create all AI microservice database tables
    """
    try:
        Base.metadata.create_all(bind=engine)
        logger.info("✅ AI Database tables created successfully")
    except Exception as e:
        logger.error(f"❌ Failed to create AI tables: {e}")
        raise 