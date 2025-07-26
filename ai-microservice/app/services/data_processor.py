from typing import Dict, List, Any, Optional
from datetime import datetime, timedelta
from sqlalchemy.orm import Session
from sqlalchemy import and_, func
from app.core.database import User, Goal, Milestone, GoalProgress, UserBehaviorMetrics

class DataProcessor:
    """
    Service class for processing and preparing data for AI analysis
    """
    
    def __init__(self, db: Session):
        self.db = db
    
    async def get_user_comprehensive_data(self, user_id: int) -> Optional[Dict[str, Any]]:
        """
        Get comprehensive user data for AI analysis
        """
        try:
            # Get user basic info
            user = self.db.query(User).filter(User.user_id == user_id).first()
            if not user:
                return None
            
            # Get user goals
            goals = self.db.query(Goal).filter(Goal.user_id == user_id).all()
            
            # Get milestones for all user goals
            goal_ids = [g.goal_id for g in goals]
            milestones = []
            if goal_ids:
                milestones = self.db.query(Milestone).filter(
                    Milestone.goal_id.in_(goal_ids)
                ).all()
            
            # Get progress data
            progress_data = []
            if goal_ids:
                progress_data = self.db.query(GoalProgress).filter(
                    GoalProgress.goal_id.in_(goal_ids)
                ).all()
            
            # Get or calculate behavior metrics
            behavior_metrics = await self._get_or_calculate_behavior_metrics(user_id)
            
            return {
                "user": self._serialize_user(user),
                "goals": [self._serialize_goal(g) for g in goals],
                "milestones": [self._serialize_milestone(m) for m in milestones],
                "progress_data": [self._serialize_progress(p) for p in progress_data],
                "behavior_metrics": behavior_metrics
            }
            
        except Exception as e:
            print(f"Error getting user data: {e}")
            return None
    
    async def get_goals_for_analysis(self, user_id: int, status_filter: Optional[str] = None) -> List[Dict[str, Any]]:
        """
        Get goals formatted for AI analysis
        """
        try:
            query = self.db.query(Goal).filter(Goal.user_id == user_id)
            
            if status_filter:
                query = query.filter(Goal.status == status_filter)
            
            goals = query.all()
            
            result = []
            for goal in goals:
                # Get milestones for this goal
                milestones = self.db.query(Milestone).filter(
                    Milestone.goal_id == goal.goal_id
                ).all()
                
                # Get progress
                progress = self.db.query(GoalProgress).filter(
                    GoalProgress.goal_id == goal.goal_id
                ).first()
                
                goal_data = self._serialize_goal(goal)
                goal_data["milestones"] = [self._serialize_milestone(m) for m in milestones]
                goal_data["current_progress"] = progress.progress_value if progress else 0
                
                result.append(goal_data)
            
            return result
            
        except Exception as e:
            print(f"Error getting goals for analysis: {e}")
            return []
    
    async def calculate_user_statistics(self, user_id: int) -> Dict[str, Any]:
        """
        Calculate comprehensive user statistics
        """
        try:
            # Get all user goals
            goals = self.db.query(Goal).filter(Goal.user_id == user_id).all()
            
            # Basic counts
            total_goals = len(goals)
            completed_goals = len([g for g in goals if g.status == 'completed'])
            in_progress_goals = len([g for g in goals if g.status == 'in_progress'])
            
            # Get all milestones
            if goals:
                goal_ids = [g.goal_id for g in goals]
                milestones = self.db.query(Milestone).filter(
                    Milestone.goal_id.in_(goal_ids)
                ).all()
                
                total_milestones = len(milestones)
                completed_milestones = len([m for m in milestones if m.is_completed])
                milestone_completion_rate = completed_milestones / total_milestones if total_milestones > 0 else 0
            else:
                total_milestones = 0
                completed_milestones = 0
                milestone_completion_rate = 0
            
            # Calculate average goal completion time
            avg_completion_time = await self._calculate_avg_completion_time(user_id)
            
            # Calculate consistency score
            consistency_score = await self._calculate_consistency_score(user_id)
            
            return {
                "user_id": user_id,
                "total_goals": total_goals,
                "completed_goals": completed_goals,
                "in_progress_goals": in_progress_goals,
                "goal_completion_rate": completed_goals / total_goals if total_goals > 0 else 0,
                "total_milestones": total_milestones,
                "completed_milestones": completed_milestones,
                "milestone_completion_rate": milestone_completion_rate,
                "avg_completion_time_days": avg_completion_time,
                "consistency_score": consistency_score
            }
            
        except Exception as e:
            print(f"Error calculating user statistics: {e}")
            return {
                "user_id": user_id,
                "total_goals": 0,
                "completed_goals": 0,
                "goal_completion_rate": 0,
                "total_milestones": 0,
                "milestone_completion_rate": 0,
                "avg_completion_time_days": 0,
                "consistency_score": 5
            }
    
    async def prepare_training_data(self, user_ids: Optional[List[int]] = None) -> List[Dict[str, Any]]:
        """
        Prepare data for ML model training
        """
        try:
            training_data = []
            
            # Get users to process
            if user_ids:
                users = self.db.query(User).filter(User.user_id.in_(user_ids)).all()
            else:
                users = self.db.query(User).limit(1000).all()  # Limit for performance
            
            for user in users:
                user_data = await self.get_user_comprehensive_data(user.user_id)
                if user_data and user_data["goals"]:
                    
                    for goal in user_data["goals"]:
                        # Create training example
                        features = self._extract_goal_features(goal, user_data)
                        outcome = self._determine_outcome(goal)
                        
                        training_example = {
                            "user_id": user.user_id,
                            "goal_id": goal["goal_id"],
                            "features": features,
                            "outcome": outcome,
                            "timestamp": datetime.now().isoformat()
                        }
                        
                        training_data.append(training_example)
            
            return training_data
            
        except Exception as e:
            print(f"Error preparing training data: {e}")
            return []
    
    # Private helper methods
    async def _get_or_calculate_behavior_metrics(self, user_id: int) -> Dict[str, Any]:
        """
        Get existing behavior metrics or calculate new ones
        """
        try:
            # Try to get recent metrics (within last week)
            week_start = datetime.now() - timedelta(days=7)
            metrics = self.db.query(UserBehaviorMetrics).filter(
                and_(
                    UserBehaviorMetrics.user_id == user_id,
                    UserBehaviorMetrics.week_start >= week_start
                )
            ).first()
            
            if metrics:
                return {
                    "session_duration": metrics.session_duration,
                    "goals_created_per_week": metrics.goals_created_per_week,
                    "milestones_completed_rate": metrics.milestones_completed_rate,
                    "avg_goal_completion_time": metrics.avg_goal_completion_time,
                    "preferred_work_hours": metrics.preferred_work_hours,
                    "goal_categories": metrics.goal_categories,
                    "procrastination_score": metrics.procrastination_score,
                    "consistency_score": metrics.consistency_score
                }
            else:
                # Calculate new metrics
                return await self._calculate_behavior_metrics(user_id)
                
        except Exception as e:
            print(f"Error getting behavior metrics: {e}")
            return await self._calculate_behavior_metrics(user_id)
    
    async def _calculate_behavior_metrics(self, user_id: int) -> Dict[str, Any]:
        """
        Calculate behavior metrics for a user
        """
        try:
            stats = await self.calculate_user_statistics(user_id)
            
            # Get goals created in last week
            week_ago = datetime.now() - timedelta(days=7)
            recent_goals = self.db.query(Goal).filter(
                and_(
                    Goal.user_id == user_id,
                    Goal.created_at >= week_ago
                )
            ).count()
            
            # Extract goal categories
            goals = self.db.query(Goal).filter(Goal.user_id == user_id).all()
            categories = self._extract_goal_categories([g.title for g in goals])
            
            return {
                "session_duration": 45,  # Default - would need activity tracking
                "goals_created_per_week": recent_goals,
                "milestones_completed_rate": stats["milestone_completion_rate"],
                "avg_goal_completion_time": stats["avg_completion_time_days"],
                "preferred_work_hours": [9, 10, 11, 14, 15],  # Default
                "goal_categories": categories,
                "procrastination_score": max(0, 10 - stats["consistency_score"]),
                "consistency_score": stats["consistency_score"]
            }
            
        except Exception as e:
            print(f"Error calculating behavior metrics: {e}")
            return {
                "session_duration": 45,
                "goals_created_per_week": 1,
                "milestones_completed_rate": 0.5,
                "avg_goal_completion_time": 30,
                "preferred_work_hours": [9, 10, 11],
                "goal_categories": ["học tập"],
                "procrastination_score": 5,
                "consistency_score": 5
            }
    
    async def _calculate_avg_completion_time(self, user_id: int) -> float:
        """
        Calculate average goal completion time in days
        """
        try:
            completed_goals = self.db.query(Goal).filter(
                and_(
                    Goal.user_id == user_id,
                    Goal.status == 'completed'
                )
            ).all()
            
            if not completed_goals:
                return 30.0  # Default
            
            total_days = 0
            count = 0
            
            for goal in completed_goals:
                if goal.start_date and goal.updated_at:
                    days = (goal.updated_at - goal.start_date).days
                    if days > 0:
                        total_days += days
                        count += 1
            
            return total_days / count if count > 0 else 30.0
            
        except Exception as e:
            print(f"Error calculating avg completion time: {e}")
            return 30.0
    
    async def _calculate_consistency_score(self, user_id: int) -> float:
        """
        Calculate user consistency score (1-10)
        """
        try:
            # Get user's goals from last 3 months
            three_months_ago = datetime.now() - timedelta(days=90)
            goals = self.db.query(Goal).filter(
                and_(
                    Goal.user_id == user_id,
                    Goal.created_at >= three_months_ago
                )
            ).all()
            
            if not goals:
                return 5.0  # Default neutral score
            
            score = 5.0  # Base score
            
            # Factor 1: Goal completion rate
            completed_count = len([g for g in goals if g.status == 'completed'])
            completion_rate = completed_count / len(goals)
            score += (completion_rate - 0.5) * 4  # +/- 2 points based on completion rate
            
            # Factor 2: Regular activity (milestone updates)
            if goals:
                goal_ids = [g.goal_id for g in goals]
                milestones = self.db.query(Milestone).filter(
                    Milestone.goal_id.in_(goal_ids)
                ).all()
                
                if milestones:
                    completed_milestones = len([m for m in milestones if m.is_completed])
                    milestone_rate = completed_milestones / len(milestones)
                    score += (milestone_rate - 0.5) * 2  # +/- 1 point
            
            return max(1.0, min(10.0, score))  # Keep between 1-10
            
        except Exception as e:
            print(f"Error calculating consistency score: {e}")
            return 5.0
    
    def _extract_goal_categories(self, goal_titles: List[str]) -> List[str]:
        """
        Extract categories from goal titles
        """
        categories = []
        category_keywords = {
            "học tập": ["học", "learn", "study", "course", "book", "read"],
            "sức khỏe": ["health", "exercise", "gym", "run", "weight", "fitness"],
            "nghề nghiệp": ["work", "career", "job", "skill", "professional"],
            "tài chính": ["money", "save", "invest", "budget", "financial"],
            "gia đình": ["family", "relationship", "parent", "child"],
            "sở thích": ["hobby", "music", "art", "travel", "photography"]
        }
        
        for title in goal_titles:
            title_lower = title.lower()
            for category, keywords in category_keywords.items():
                if any(keyword in title_lower for keyword in keywords):
                    if category not in categories:
                        categories.append(category)
        
        return categories if categories else ["khác"]
    
    def _extract_goal_features(self, goal: Dict[str, Any], user_data: Dict[str, Any]) -> Dict[str, Any]:
        """
        Extract features for ML training
        """
        features = {
            "title_length": len(goal.get("title", "")),
            "description_length": len(goal.get("description", "")),
            "has_description": bool(goal.get("description")),
            "timeline_days": self._calculate_timeline_days(goal),
            "milestone_count": len(goal.get("milestones", [])),
            "user_experience": len(user_data.get("goals", [])),  # Number of goals as experience proxy
            "category": self._categorize_goal(goal.get("title", ""))
        }
        return features
    
    def _determine_outcome(self, goal: Dict[str, Any]) -> str:
        """
        Determine goal outcome for training
        """
        status = goal.get("status", "new")
        if status == "completed":
            return "success"
        elif status == "cancelled":
            return "failed"
        else:
            # For in-progress goals, use progress to estimate
            progress = goal.get("current_progress", 0)
            if progress > 80:
                return "success"
            elif progress > 30:
                return "partial"
            else:
                return "failed"
    
    def _calculate_timeline_days(self, goal: Dict[str, Any]) -> int:
        """
        Calculate timeline in days
        """
        try:
            start_date = goal.get("start_date")
            end_date = goal.get("end_date")
            
            if start_date and end_date:
                start_dt = datetime.fromisoformat(start_date.replace('Z', '+00:00'))
                end_dt = datetime.fromisoformat(end_date.replace('Z', '+00:00'))
                return (end_dt - start_dt).days
        except:
            pass
        return 30  # Default
    
    def _categorize_goal(self, title: str) -> str:
        """
        Categorize goal based on title
        """
        categories = self._extract_goal_categories([title])
        return categories[0] if categories else "khác"
    
    # Serialization methods
    def _serialize_user(self, user: User) -> Dict[str, Any]:
        """Serialize user object"""
        return {
            "user_id": user.user_id,
            "display_name": user.display_name,
            "email": user.email,
            "created_at": user.created_at.isoformat() if user.created_at else None,
            "last_login_at": user.last_login_at.isoformat() if user.last_login_at else None
        }
    
    def _serialize_goal(self, goal: Goal) -> Dict[str, Any]:
        """Serialize goal object"""
        return {
            "goal_id": goal.goal_id,
            "user_id": goal.user_id,
            "title": goal.title,
            "description": goal.description,
            "start_date": goal.start_date.isoformat() if goal.start_date else None,
            "end_date": goal.end_date.isoformat() if goal.end_date else None,
            "status": goal.status,
            "created_at": goal.created_at.isoformat() if goal.created_at else None,
            "updated_at": goal.updated_at.isoformat() if goal.updated_at else None
        }
    
    def _serialize_milestone(self, milestone: Milestone) -> Dict[str, Any]:
        """Serialize milestone object"""
        return {
            "milestone_id": milestone.milestone_id,
            "goal_id": milestone.goal_id,
            "title": milestone.title,
            "deadline": milestone.deadline.isoformat() if milestone.deadline else None,
            "is_completed": milestone.is_completed,
            "created_at": milestone.created_at.isoformat() if milestone.created_at else None,
            "updated_at": milestone.updated_at.isoformat() if milestone.updated_at else None
        }
    
    def _serialize_progress(self, progress: GoalProgress) -> Dict[str, Any]:
        """Serialize progress object"""
        return {
            "progress_id": progress.progress_id,
            "goal_id": progress.goal_id,
            "progress_value": progress.progress_value,
            "updated_at": progress.updated_at.isoformat() if progress.updated_at else None
        } 