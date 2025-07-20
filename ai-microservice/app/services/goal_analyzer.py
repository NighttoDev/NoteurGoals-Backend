import logging
from typing import Dict, List, Any, Optional
from datetime import datetime, timedelta
import json
import math
from dataclasses import dataclass
from sqlalchemy.orm import Session
from app.core.database import GoalAnalysisResult, UserBehaviorMetrics, UserInsights
import structlog

logger = structlog.get_logger()

@dataclass
class GoalData:
    """Goal data structure received from Laravel API"""
    id: int
    user_id: int
    title: str
    description: str
    start_date: str
    end_date: str
    status: str
    milestones: List[Dict[str, Any]] = None

@dataclass 
class UserData:
    """User data structure received from Laravel API"""
    id: int
    name: str
    email: str
    goals_count: int = 0
    completed_goals: int = 0

class GoalAnalyzer:
    """
    AI-powered goal analysis service.
    Receives data from Laravel API, performs analysis, stores results in AI database.
    """
    
    def __init__(self, db: Session):
        self.db = db
        logger.info("GoalAnalyzer initialized")
    
    def analyze_goal_breakdown(self, goal_data: Dict[str, Any], user_context: Dict[str, Any] = None) -> Dict[str, Any]:
        """
        Analyze goal and provide breakdown suggestions
        
        Args:
            goal_data: Goal information from Laravel API
            user_context: Additional user context from Laravel
            
        Returns:
            Analysis results with breakdown suggestions
        """
        try:
            start_time = datetime.now()
            
            # Parse goal data
            goal = GoalData(**goal_data)
            
            # Perform complexity analysis
            complexity_score = self._calculate_complexity_score(goal)
            
            # Generate milestone suggestions  
            suggested_milestones = self._generate_milestone_suggestions(goal, complexity_score)
            
            # Estimate duration
            estimated_duration = self._estimate_duration(goal, complexity_score)
            
            # Generate recommendations
            recommendations = self._generate_recommendations(goal, complexity_score)
            
            # Prepare results
            results = {
                "complexity_score": complexity_score,
                "difficulty_level": self._get_difficulty_level(complexity_score),
                "estimated_duration_days": estimated_duration,
                "suggested_milestones": suggested_milestones,
                "recommendations": recommendations,
                "success_probability": self._calculate_success_probability(goal, complexity_score)
            }
            
            # Calculate processing time
            processing_time = (datetime.now() - start_time).total_seconds() * 1000
            
            # Store analysis result in AI database
            self._store_analysis_result(
                goal_id=goal.id,
                user_id=goal.user_id,
                analysis_type="goal_breakdown",
                input_data=goal_data,
                results=results,
                processing_time_ms=int(processing_time)
            )
            
            logger.info(f"Goal breakdown analysis completed for goal {goal.id}")
            return results
            
        except Exception as e:
            logger.error(f"Goal breakdown analysis failed: {e}")
            raise
    
    def analyze_priority_suggestions(self, goals_data: List[Dict[str, Any]], user_context: Dict[str, Any] = None) -> Dict[str, Any]:
        """
        Analyze multiple goals and suggest priority ranking
        
        Args:
            goals_data: List of goals from Laravel API
            user_context: User context information
            
        Returns:
            Priority analysis results
        """
        try:
            start_time = datetime.now()
            
            prioritized_goals = []
            
            for goal_data in goals_data:
                goal = GoalData(**goal_data)
                priority_score = self._calculate_priority_score(goal, user_context)
                
                prioritized_goals.append({
                    "goal_id": goal.id,
                    "title": goal.title,
                    "priority_score": priority_score,
                    "urgency_level": self._get_urgency_level(priority_score),
                    "reasons": self._get_priority_reasons(goal, priority_score)
                })
            
            # Sort by priority score (highest first)
            prioritized_goals.sort(key=lambda x: x["priority_score"], reverse=True)
            
            # Identify urgent goals
            urgent_goals = [g for g in prioritized_goals if g["priority_score"] > 7.5]
            
            results = {
                "prioritized_goals": prioritized_goals,
                "urgent_goals": urgent_goals,
                "total_goals": len(goals_data),
                "priority_distribution": self._calculate_priority_distribution(prioritized_goals)
            }
            
            processing_time = (datetime.now() - start_time).total_seconds() * 1000
            
            # Store analysis for the user
            user_id = user_context.get("user_id") if user_context else None
            if user_id:
                self._store_analysis_result(
                    goal_id=None,  # Multiple goals
                    user_id=user_id,
                    analysis_type="priority_suggestions",
                    input_data={"goals": goals_data, "user_context": user_context},
                    results=results,
                    processing_time_ms=int(processing_time)
                )
            
            logger.info(f"Priority analysis completed for {len(goals_data)} goals")
            return results
            
        except Exception as e:
            logger.error(f"Priority analysis failed: {e}")
            raise
    
    def predict_completion_forecast(self, goals_data: List[Dict[str, Any]], user_context: Dict[str, Any] = None) -> Dict[str, Any]:
        """
        Predict completion probability and timeline for goals
        
        Args:
            goals_data: List of goals from Laravel API
            user_context: User context information
            
        Returns:
            Completion forecast results
        """
        try:
            start_time = datetime.now()
            
            forecasts = []
            
            for goal_data in goals_data:
                goal = GoalData(**goal_data)
                
                completion_probability = self._predict_completion_probability(goal, user_context)
                estimated_completion_date = self._estimate_completion_date(goal, user_context)
                risk_factors = self._identify_risk_factors(goal, user_context)
                
                forecasts.append({
                    "goal_id": goal.id,
                    "title": goal.title,
                    "completion_probability": completion_probability,
                    "estimated_completion_date": estimated_completion_date,
                    "risk_factors": risk_factors,
                    "confidence_level": self._calculate_confidence_level(goal, user_context)
                })
            
            # Overall statistics
            avg_completion_probability = sum(f["completion_probability"] for f in forecasts) / len(forecasts)
            high_risk_goals = [f for f in forecasts if f["completion_probability"] < 0.5]
            
            results = {
                "goal_forecasts": forecasts,
                "overall_success_rate": avg_completion_probability,
                "high_risk_goals": high_risk_goals,
                "recommendations": self._generate_forecast_recommendations(forecasts)
            }
            
            processing_time = (datetime.now() - start_time).total_seconds() * 1000
            
            user_id = user_context.get("user_id") if user_context else None
            if user_id:
                self._store_analysis_result(
                    goal_id=None,
                    user_id=user_id,
                    analysis_type="completion_forecast",
                    input_data={"goals": goals_data, "user_context": user_context},
                    results=results,
                    processing_time_ms=int(processing_time)
                )
            
            logger.info(f"Completion forecast completed for {len(goals_data)} goals")
            return results
            
        except Exception as e:
            logger.error(f"Completion forecast failed: {e}")
            raise
    
    def generate_user_insights(self, user_id: int, user_context: Dict[str, Any] = None) -> Dict[str, Any]:
        """
        Generate comprehensive user insights and patterns
        
        Args:
            user_id: Laravel user ID
            user_context: Additional user data from Laravel
            
        Returns:
            User insights and recommendations
        """
        try:
            start_time = datetime.now()
            
            # Get user's historical analysis data
            historical_data = self._get_user_historical_data(user_id)
            
            # Analyze productivity patterns
            productivity_insights = self._analyze_productivity_patterns(user_id, user_context, historical_data)
            
            # Generate behavioral insights
            behavioral_insights = self._analyze_user_behavior(user_id, user_context, historical_data)
            
            # Generate recommendations
            recommendations = self._generate_user_recommendations(user_id, productivity_insights, behavioral_insights)
            
            results = {
                "user_id": user_id,
                "productivity_insights": productivity_insights,
                "behavioral_insights": behavioral_insights,
                "recommendations": recommendations,
                "analysis_period": "last_30_days",
                "generated_at": datetime.now().isoformat()
            }
            
            processing_time = (datetime.now() - start_time).total_seconds() * 1000
            
            # Store insights
            self._store_user_insights(user_id, results, processing_time)
            
            logger.info(f"User insights generated for user {user_id}")
            return results
            
        except Exception as e:
            logger.error(f"User insights generation failed: {e}")
            raise
    
    # Private helper methods
    def _calculate_complexity_score(self, goal: GoalData) -> float:
        """Calculate goal complexity score (0-10)"""
        score = 0
        
        # Description length and complexity
        description_words = len(goal.description.split())
        score += min(description_words / 20, 3)  # Max 3 points
        
        # Title complexity
        title_words = len(goal.title.split())
        score += min(title_words / 5, 2)  # Max 2 points
        
        # Duration factor
        if goal.start_date and goal.end_date:
            try:
                start = datetime.fromisoformat(goal.start_date.replace('Z', '+00:00'))
                end = datetime.fromisoformat(goal.end_date.replace('Z', '+00:00'))
                duration_days = (end - start).days
                
                if duration_days > 365:  # Very long-term
                    score += 4
                elif duration_days > 90:  # Medium-term
                    score += 2
                else:  # Short-term
                    score += 1
            except:
                score += 2  # Default if parsing fails
        
        # Milestone factor
        if goal.milestones:
            milestone_count = len(goal.milestones)
            score += min(milestone_count / 3, 1)  # Max 1 point
        
        return min(score, 10)  # Cap at 10
    
    def _get_difficulty_level(self, complexity_score: float) -> str:
        """Get difficulty level based on complexity score"""
        if complexity_score <= 3:
            return "easy"
        elif complexity_score <= 6:
            return "medium"
        elif complexity_score <= 8:
            return "hard"
        else:
            return "expert"
    
    def _generate_milestone_suggestions(self, goal: GoalData, complexity_score: float) -> List[Dict[str, Any]]:
        """Generate milestone suggestions based on goal analysis"""
        
        suggested_count = max(3, min(int(complexity_score), 8))
        
        milestones = []
        
        # Generate basic milestone structure
        for i in range(suggested_count):
            progress_percentage = (i + 1) * (100 / suggested_count)
            milestones.append({
                "title": f"Milestone {i + 1}: {progress_percentage:.0f}% Progress",
                "description": f"Complete {progress_percentage:.0f}% of goal objectives",
                "target_percentage": progress_percentage,
                "estimated_duration_days": int((i + 1) * (30 / suggested_count))
            })
        
        return milestones
    
    def _estimate_duration(self, goal: GoalData, complexity_score: float) -> int:
        """Estimate goal duration in days"""
        
        base_duration = 30  # 30 days base
        complexity_multiplier = 1 + (complexity_score / 10)
        
        estimated_days = int(base_duration * complexity_multiplier)
        
        return max(7, min(estimated_days, 365))  # Between 7 days and 1 year
    
    def _generate_recommendations(self, goal: GoalData, complexity_score: float) -> List[str]:
        """Generate actionable recommendations"""
        
        recommendations = []
        
        if complexity_score > 7:
            recommendations.append("Consider breaking this goal into smaller sub-goals")
            recommendations.append("Allocate extra time for planning and research")
        
        if complexity_score < 3:
            recommendations.append("This goal can be completed quickly - consider adding more ambitious targets")
        
        recommendations.append("Set regular check-in dates to track progress")
        recommendations.append("Identify potential obstacles early and plan solutions")
        
        return recommendations
    
    def _calculate_success_probability(self, goal: GoalData, complexity_score: float) -> float:
        """Calculate probability of success (0-1)"""
        
        base_probability = 0.7  # 70% base success rate
        
        # Adjust based on complexity
        complexity_penalty = (complexity_score - 5) * 0.05
        probability = base_probability - complexity_penalty
        
        return max(0.1, min(probability, 0.95))  # Between 10% and 95%
    
    def _calculate_priority_score(self, goal: GoalData, user_context: Dict[str, Any] = None) -> float:
        """Calculate priority score (0-10)"""
        
        score = 5.0  # Base score
        
        # Time urgency
        if goal.end_date:
            try:
                end_date = datetime.fromisoformat(goal.end_date.replace('Z', '+00:00'))
                days_until_deadline = (end_date - datetime.now()).days
                
                if days_until_deadline < 7:
                    score += 3
                elif days_until_deadline < 30:
                    score += 2
                elif days_until_deadline < 90:
                    score += 1
            except:
                pass
        
        # Goal status
        if goal.status == "in_progress":
            score += 1
        elif goal.status == "overdue":
            score += 2.5
        
        return min(score, 10)
    
    def _get_urgency_level(self, priority_score: float) -> str:
        """Get urgency level based on priority score"""
        if priority_score >= 8:
            return "critical"
        elif priority_score >= 6:
            return "high" 
        elif priority_score >= 4:
            return "medium"
        else:
            return "low"
    
    def _get_priority_reasons(self, goal: GoalData, priority_score: float) -> List[str]:
        """Get reasons for priority score"""
        reasons = []
        
        if goal.status == "overdue":
            reasons.append("Goal is overdue")
        
        if goal.end_date:
            try:
                end_date = datetime.fromisoformat(goal.end_date.replace('Z', '+00:00'))
                days_until_deadline = (end_date - datetime.now()).days
                
                if days_until_deadline < 7:
                    reasons.append("Deadline is within a week")
                elif days_until_deadline < 30:
                    reasons.append("Deadline is approaching soon")
            except:
                pass
        
        if not reasons:
            reasons.append("Standard priority assessment")
        
        return reasons
    
    def _calculate_priority_distribution(self, prioritized_goals: List[Dict[str, Any]]) -> Dict[str, int]:
        """Calculate distribution of priority levels"""
        distribution = {"critical": 0, "high": 0, "medium": 0, "low": 0}
        
        for goal in prioritized_goals:
            urgency = goal["urgency_level"]
            distribution[urgency] += 1
        
        return distribution
    
    def _predict_completion_probability(self, goal: GoalData, user_context: Dict[str, Any] = None) -> float:
        """Predict completion probability for a goal"""
        
        # Use complexity score as base
        complexity_score = self._calculate_complexity_score(goal)
        base_probability = self._calculate_success_probability(goal, complexity_score)
        
        # Adjust based on user context
        if user_context:
            user_success_rate = user_context.get("historical_success_rate", 0.7)
            base_probability = (base_probability + user_success_rate) / 2
        
        return base_probability
    
    def _estimate_completion_date(self, goal: GoalData, user_context: Dict[str, Any] = None) -> str:
        """Estimate completion date"""
        
        complexity_score = self._calculate_complexity_score(goal)
        estimated_days = self._estimate_duration(goal, complexity_score)
        
        completion_date = datetime.now() + timedelta(days=estimated_days)
        return completion_date.isoformat()
    
    def _identify_risk_factors(self, goal: GoalData, user_context: Dict[str, Any] = None) -> List[str]:
        """Identify potential risk factors"""
        
        risks = []
        complexity_score = self._calculate_complexity_score(goal)
        
        if complexity_score > 7:
            risks.append("High complexity may lead to scope creep")
        
        if goal.end_date:
            try:
                end_date = datetime.fromisoformat(goal.end_date.replace('Z', '+00:00'))
                days_until_deadline = (end_date - datetime.now()).days
                
                if days_until_deadline < 30:
                    risks.append("Tight deadline may affect quality")
            except:
                pass
        
        if not goal.milestones:
            risks.append("Lack of milestones makes tracking difficult")
        
        return risks
    
    def _calculate_confidence_level(self, goal: GoalData, user_context: Dict[str, Any] = None) -> float:
        """Calculate confidence level of predictions"""
        
        # Base confidence
        confidence = 0.7
        
        # Adjust based on available data
        if user_context and user_context.get("historical_data_points", 0) > 10:
            confidence += 0.2
        
        if goal.milestones:
            confidence += 0.1
        
        return min(confidence, 0.95)
    
    def _generate_forecast_recommendations(self, forecasts: List[Dict[str, Any]]) -> List[str]:
        """Generate recommendations based on forecasts"""
        
        recommendations = []
        
        high_risk_count = len([f for f in forecasts if f["completion_probability"] < 0.5])
        
        if high_risk_count > 0:
            recommendations.append(f"Focus on {high_risk_count} high-risk goals to improve success rate")
        
        recommendations.append("Regular progress reviews recommended for all goals")
        recommendations.append("Consider goal prioritization based on success probability")
        
        return recommendations
    
    def _get_user_historical_data(self, user_id: int) -> List[Dict[str, Any]]:
        """Get user's historical analysis data from AI database"""
        
        try:
            # Query user's past analysis results
            historical_analyses = self.db.query(GoalAnalysisResult).filter(
                GoalAnalysisResult.user_id == user_id
            ).order_by(GoalAnalysisResult.created_at.desc()).limit(50).all()
            
            return [
                {
                    "analysis_type": analysis.analysis_type,
                    "results": analysis.results,
                    "confidence_score": analysis.confidence_score,
                    "created_at": analysis.created_at.isoformat()
                }
                for analysis in historical_analyses
            ]
        
        except Exception as e:
            logger.error(f"Error fetching historical data for user {user_id}: {e}")
            return []
    
    def _analyze_productivity_patterns(self, user_id: int, user_context: Dict[str, Any], historical_data: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Analyze user productivity patterns"""
        
        # Analyze completion rates
        completion_analyses = [h for h in historical_data if h["analysis_type"] == "completion_forecast"]
        
        if completion_analyses:
            avg_success_rate = sum(
                h["results"].get("overall_success_rate", 0.5) 
                for h in completion_analyses
            ) / len(completion_analyses)
        else:
            avg_success_rate = 0.7  # Default
        
        return {
            "average_success_rate": avg_success_rate,
            "total_analyses": len(historical_data),
            "productivity_trend": "stable",  # Could be calculated based on historical data
            "best_performing_goal_types": ["short_term", "well_defined"],
            "improvement_areas": ["long_term_planning", "milestone_tracking"]
        }
    
    def _analyze_user_behavior(self, user_id: int, user_context: Dict[str, Any], historical_data: List[Dict[str, Any]]) -> Dict[str, Any]:
        """Analyze user behavioral patterns"""
        
        # Get behavior metrics if available
        behavior_metrics = self.db.query(UserBehaviorMetrics).filter(
            UserBehaviorMetrics.user_id == user_id
        ).first()
        
        if behavior_metrics:
            return {
                "goals_created": behavior_metrics.goals_created_count,
                "goals_completed": behavior_metrics.goals_completed_count,
                "completion_rate": behavior_metrics.goals_completed_count / max(behavior_metrics.goals_created_count, 1),
                "productivity_score": behavior_metrics.productivity_score,
                "consistency_pattern": "regular"  # Could be calculated
            }
        else:
            return {
                "goals_created": user_context.get("goals_count", 0),
                "goals_completed": user_context.get("completed_goals", 0),
                "completion_rate": 0.7,
                "productivity_score": 7.5,
                "consistency_pattern": "unknown"
            }
    
    def _generate_user_recommendations(self, user_id: int, productivity_insights: Dict[str, Any], behavioral_insights: Dict[str, Any]) -> List[str]:
        """Generate personalized recommendations for user"""
        
        recommendations = []
        
        completion_rate = behavioral_insights.get("completion_rate", 0.7)
        
        if completion_rate < 0.5:
            recommendations.append("Focus on completing smaller, achievable goals to build momentum")
        elif completion_rate > 0.8:
            recommendations.append("Consider taking on more challenging goals to maximize growth")
        
        if productivity_insights.get("average_success_rate", 0.7) < 0.6:
            recommendations.append("Break down complex goals into smaller milestones")
        
        recommendations.append("Set regular review sessions to track progress")
        recommendations.append("Use time-blocking to dedicate focused time to goal work")
        
        return recommendations
    
    def _store_analysis_result(self, goal_id: Optional[int], user_id: int, analysis_type: str, 
                              input_data: Dict[str, Any], results: Dict[str, Any], 
                              processing_time_ms: int, confidence_score: float = 0.8):
        """Store analysis result in AI database"""
        
        try:
            analysis_result = GoalAnalysisResult(
                goal_id=goal_id,
                user_id=user_id,
                analysis_type=analysis_type,
                input_data=input_data,
                results=results,
                confidence_score=confidence_score,
                processing_time_ms=processing_time_ms
            )
            
            self.db.add(analysis_result)
            self.db.commit()
            
            logger.info(f"Analysis result stored: {analysis_type} for user {user_id}")
        
        except Exception as e:
            logger.error(f"Failed to store analysis result: {e}")
            self.db.rollback()
    
    def _store_user_insights(self, user_id: int, insights: Dict[str, Any], processing_time_ms: int):
        """Store user insights in AI database"""
        
        try:
            user_insights = UserInsights(
                user_id=user_id,
                insight_type="comprehensive",
                insights=insights,
                confidence_score=0.8
            )
            
            self.db.add(user_insights)
            self.db.commit()
            
            logger.info(f"User insights stored for user {user_id}")
        
        except Exception as e:
            logger.error(f"Failed to store user insights: {e}")
            self.db.rollback() 