#!/usr/bin/env python3
"""
Setup script for AI Microservice
"""

import os
import sys
import subprocess

def run_command(command, description):
    """Run a command and handle errors"""
    print(f"📦 {description}...")
    try:
        result = subprocess.run(command, shell=True, check=True, capture_output=True, text=True)
        print(f"✅ {description} completed successfully")
        return True
    except subprocess.CalledProcessError as e:
        print(f"❌ {description} failed: {e.stderr}")
        return False

def create_env_file():
    """Create .env file from template"""
    env_content = """# Database
DATABASE_URL=mysql://root:password@localhost:3306/GoalManagementSystem
DATABASE_ECHO=false

# Redis
REDIS_URL=redis://localhost:6379
REDIS_DB=0

# AI/ML Settings
ML_MODEL_PATH=/app/ml_models/trained_models
MODEL_CACHE_TTL=3600
PREDICTION_CONFIDENCE_THRESHOLD=0.7

# API Settings
API_TIMEOUT=30
MAX_BATCH_SIZE=100

# Security
API_KEY=your_secret_api_key_here
ALLOWED_HOSTS=["*"]

# Logging
LOG_LEVEL=INFO
LOG_FORMAT=json

# Performance
ENABLE_CACHING=true
CACHE_TTL=3600

# Development
DEBUG=false
"""
    
    if not os.path.exists('.env'):
        with open('.env', 'w') as f:
            f.write(env_content)
        print("✅ Created .env file")
    else:
        print("ℹ️  .env file already exists")

def setup_ai_microservice():
    """Main setup function"""
    print("🚀 Setting up AI Microservice for Goal Management...")
    print("=" * 60)
    
    # Check Python version
    if sys.version_info < (3, 8):
        print("❌ Python 3.8+ is required")
        sys.exit(1)
    
    print(f"✅ Python {sys.version_info.major}.{sys.version_info.minor} detected")
    
    # Create virtual environment
    if not os.path.exists('venv'):
        if not run_command("python -m venv venv", "Creating virtual environment"):
            sys.exit(1)
    else:
        print("ℹ️  Virtual environment already exists")
    
    # Activate virtual environment and install dependencies
    if os.name == 'nt':  # Windows
        activate_cmd = "venv\\Scripts\\activate && "
    else:  # Unix/Linux/Mac
        activate_cmd = "source venv/bin/activate && "
    
    if not run_command(f"{activate_cmd}pip install --upgrade pip", "Upgrading pip"):
        sys.exit(1)
    
    if not run_command(f"{activate_cmd}pip install -r requirements.txt", "Installing dependencies"):
        sys.exit(1)
    
    # Create directories
    os.makedirs("ml_models/trained_models", exist_ok=True)
    os.makedirs("training_data", exist_ok=True)
    os.makedirs("logs", exist_ok=True)
    print("✅ Created necessary directories")
    
    # Create .env file
    create_env_file()
    
    # Create __init__.py files for proper imports
    init_files = [
        "app/__init__.py",
        "app/api/__init__.py",
        "app/api/endpoints/__init__.py",
        "app/core/__init__.py",
        "app/services/__init__.py",
        "app/models/__init__.py",
        "app/utils/__init__.py"
    ]
    
    for init_file in init_files:
        os.makedirs(os.path.dirname(init_file), exist_ok=True)
        if not os.path.exists(init_file):
            with open(init_file, 'w') as f:
                f.write("# This file makes Python treat the directory as a package\n")
    
    print("✅ Created __init__.py files")
    
    print("\n🎉 Setup completed successfully!")
    print("\n📋 Next steps:")
    print("1. Update database connection in .env file")
    print("2. Make sure MySQL and Redis are running")
    print("3. Run the AI service:")
    if os.name == 'nt':
        print("   venv\\Scripts\\activate")
    else:
        print("   source venv/bin/activate")
    print("   python -m uvicorn app.main:app --reload --host 0.0.0.0 --port 8001")
    print("\n4. Test the service:")
    print("   curl http://localhost:8001/health")
    print("   Open http://localhost:8001/docs for API documentation")

if __name__ == "__main__":
    setup_ai_microservice() 