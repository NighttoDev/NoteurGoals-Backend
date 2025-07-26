from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from contextlib import asynccontextmanager
import structlog
from prometheus_client import generate_latest, CONTENT_TYPE_LATEST
from fastapi.responses import Response
import os

# Import our API routes
from app.api.endpoints import analysis, predictions, recommendations, health
from app.core.config import get_settings
from app.core.database import engine, Base

# Setup structured logging
logger = structlog.get_logger()

@asynccontextmanager
async def lifespan(app: FastAPI):
    """
    Startup and shutdown events
    """
    # Startup
    logger.info("🚀 Starting AI Goal Analysis Service...")
    
    # Create database tables
    try:
        Base.metadata.create_all(bind=engine)
        logger.info("✅ Database tables created successfully")
    except Exception as e:
        logger.error(f"❌ Database connection failed: {e}")
    
    yield
    
    # Shutdown
    logger.info("🛑 Shutting down AI Goal Analysis Service...")

# Get settings
settings = get_settings()

# Create FastAPI application
app = FastAPI(
    title=settings.project_name,
    version=settings.version,
    description="AI-powered goal analysis and recommendations service",
    lifespan=lifespan
)

# Setup CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.get_cors_origins(),
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Include API routes
app.include_router(health.router, prefix="/health", tags=["health"])
app.include_router(analysis.router, prefix=settings.api_v1_prefix + "/analysis", tags=["analysis"])
app.include_router(predictions.router, prefix=settings.api_v1_prefix + "/predictions", tags=["predictions"])
app.include_router(recommendations.router, prefix=settings.api_v1_prefix + "/recommendations", tags=["recommendations"])

# Root endpoint
@app.get("/")
async def root():
    return {
        "message": "AI Goal Analysis Service",
        "version": settings.version,
        "status": "running"
    }

# Health check endpoint
@app.get("/health")
async def health_check():
    return {
        "status": "healthy",
        "service": settings.project_name,
        "version": settings.version
    }

# Metrics endpoint
@app.get("/metrics")
async def metrics():
    """
    Prometheus metrics endpoint
    """
    try:
        return Response(generate_latest(), media_type=CONTENT_TYPE_LATEST)
    except Exception as e:
        logger.error(f"Error generating metrics: {e}")
        raise HTTPException(status_code=500, detail="Could not generate metrics")

if __name__ == "__main__":
    import uvicorn
    
    uvicorn.run(
        "app.main:app",
        host="0.0.0.0",
        port=8000,
        reload=settings.debug,
        log_level=settings.log_level.lower()
    ) 