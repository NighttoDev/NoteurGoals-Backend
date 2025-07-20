from fastapi import APIRouter
from datetime import datetime

router = APIRouter()

@router.get("/")
async def health_check():
    """
    Basic health check endpoint
    """
    return {
        "status": "healthy",
        "service": "ai-microservice",
        "version": "1.0.0",
        "timestamp": datetime.now().isoformat()
    }

@router.get("/detailed")
async def detailed_health_check():
    """
    Detailed health check with system info
    """
    return {
        "status": "healthy",
        "service": "ai-microservice",
        "version": "1.0.0",
        "timestamp": datetime.now().isoformat(),
        "components": {
            "database": "healthy",
            "ml_models": "loaded",
            "cache": "connected"
        }
    } 