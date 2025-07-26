from fastapi import APIRouter, HTTPException, Depends
from pydantic import BaseModel
from typing import List, Dict, Any, Optional
from datetime import datetime
import structlog
from sqlalchemy.orm import Session

from app.core.database import get_db
from app.services.goal_analyzer import GoalAnalyzer
from app.services.data_processor import DataProcessor

logger = structlog.get_logger()
router = APIRouter()

# Request/Response Models
class GoalAnalysisRequest(BaseModel):
    goal_id: int
    title: str
    description: str
    start_date: str
    end_date: str
    user_context: Optional[Dict[str, Any]] = {}

class UserGoalData(BaseModel):
    user_id: int
    goals: List[Dict[str, Any]]
    milestones: List[Dict[str, Any]]
    behavior_metrics: Dict[str, Any]

class AnalysisResponse(BaseModel):
    goal_id: Optional[int] = None
    user_id: Optional[int] = None
    analysis_type: str
    results: Dict[str, Any]
    confidence: float
    timestamp: datetime

@router.post("/goal-breakdown", response_model=AnalysisResponse)
async def analyze_goal_breakdown(
    request: GoalAnalysisRequest,
    db: Session = Depends(get_db)
):
    """
    Phân tích và chia nhỏ mục tiêu thành các bước cụ thể
    """
    try:
        logger.info(f"Analyzing goal breakdown for goal_id: {request.goal_id}")
        
        analyzer = GoalAnalyzer(db)
        
        # Perform goal breakdown analysis
        analysis_result = await analyzer.analyze_goal_breakdown(
            goal_id=request.goal_id,
            title=request.title,
            description=request.description,
            start_date=request.start_date,
            end_date=request.end_date,
            user_context=request.user_context
        )
        
        return AnalysisResponse(
            goal_id=request.goal_id,
            analysis_type="goal_breakdown",
            results=analysis_result,
            confidence=analysis_result.get("confidence", 0.7),
            timestamp=datetime.now()
        )
        
    except Exception as e:
        logger.error(f"Goal breakdown analysis failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/priority-suggestions", response_model=AnalysisResponse)
async def get_priority_suggestions(
    user_data: UserGoalData,
    db: Session = Depends(get_db)
):
    """
    Đưa ra gợi ý ưu tiên mục tiêu dựa trên context người dùng
    """
    try:
        logger.info(f"Generating priority suggestions for user_id: {user_data.user_id}")
        
        analyzer = GoalAnalyzer(db)
        
        # Generate priority suggestions
        priority_result = await analyzer.analyze_goal_priorities(
            user_id=user_data.user_id,
            goals=user_data.goals,
            behavior_metrics=user_data.behavior_metrics
        )
        
        return AnalysisResponse(
            user_id=user_data.user_id,
            analysis_type="priority_suggestions",
            results=priority_result,
            confidence=priority_result.get("confidence", 0.7),
            timestamp=datetime.now()
        )
        
    except Exception as e:
        logger.error(f"Priority suggestions failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/completion-forecast", response_model=AnalysisResponse)
async def predict_completion(
    user_data: UserGoalData,
    db: Session = Depends(get_db)
):
    """
    Dự đoán khả năng hoàn thành mục tiêu
    """
    try:
        logger.info(f"Predicting completion for user_id: {user_data.user_id}")
        
        analyzer = GoalAnalyzer(db)
        
        # Predict completion
        completion_result = await analyzer.predict_goal_completion(
            user_id=user_data.user_id,
            goals=user_data.goals,
            milestones=user_data.milestones,
            behavior_metrics=user_data.behavior_metrics
        )
        
        return AnalysisResponse(
            user_id=user_data.user_id,
            analysis_type="completion_forecast",
            results=completion_result,
            confidence=completion_result.get("confidence", 0.7),
            timestamp=datetime.now()
        )
        
    except Exception as e:
        logger.error(f"Completion forecast failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/user-insights/{user_id}", response_model=AnalysisResponse)
async def get_user_insights(
    user_id: int,
    db: Session = Depends(get_db)
):
    """
    Phân tích tổng quan về patterns và insights của user
    """
    try:
        logger.info(f"Generating user insights for user_id: {user_id}")
        
        # Get user data
        data_processor = DataProcessor(db)
        user_data = await data_processor.get_user_comprehensive_data(user_id)
        
        if not user_data:
            raise HTTPException(status_code=404, detail="User not found")
        
        analyzer = GoalAnalyzer(db)
        
        # Generate insights
        insights_result = await analyzer.generate_user_insights(user_id, user_data)
        
        return AnalysisResponse(
            user_id=user_id,
            analysis_type="user_insights",
            results=insights_result,
            confidence=insights_result.get("confidence", 0.7),
            timestamp=datetime.now()
        )
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"User insights generation failed: {e}")
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/batch-analyze-users")
async def batch_analyze_users(
    user_ids: List[int],
    db: Session = Depends(get_db)
):
    """
    Xử lý batch analysis cho multiple users
    """
    try:
        logger.info(f"Batch analyzing {len(user_ids)} users")
        
        if len(user_ids) > 100:  # Limit batch size
            raise HTTPException(status_code=400, detail="Batch size too large (max 100)")
        
        analyzer = GoalAnalyzer(db)
        data_processor = DataProcessor(db)
        
        results = []
        
        for user_id in user_ids:
            try:
                # Get user data
                user_data = await data_processor.get_user_comprehensive_data(user_id)
                
                if user_data:
                    # Generate insights
                    insights = await analyzer.generate_user_insights(user_id, user_data)
                    results.append({
                        "user_id": user_id,
                        "status": "success",
                        "insights": insights
                    })
                else:
                    results.append({
                        "user_id": user_id,
                        "status": "not_found",
                        "insights": None
                    })
                    
            except Exception as e:
                logger.error(f"Failed to analyze user {user_id}: {e}")
                results.append({
                    "user_id": user_id,
                    "status": "error",
                    "error": str(e),
                    "insights": None
                })
        
        return {
            "total_users": len(user_ids),
            "processed": len(results),
            "results": results,
            "batch_id": f"batch_{datetime.now().isoformat()}",
            "processed_at": datetime.now().isoformat()
        }
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Batch analysis failed: {e}")
        raise HTTPException(status_code=500, detail=str(e)) 