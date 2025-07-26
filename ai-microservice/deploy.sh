#!/bin/bash

# AI Microservice Deployment Script
echo "🚀 Deploying AI Microservice..."

# Set environment
export ENVIRONMENT=production

# Install dependencies
echo "📦 Installing dependencies..."
pip install -r requirements.txt

# Run database migrations (if needed)
echo "🗄️ Setting up database..."
# python -m alembic upgrade head

# Start the application
echo "🔥 Starting AI service..."
if [ "$1" == "production" ]; then
    # Production mode with gunicorn
    gunicorn app.main:app \
        --workers 4 \
        --worker-class uvicorn.workers.UvicornWorker \
        --bind 0.0.0.0:8000 \
        --timeout 30 \
        --access-logfile - \
        --error-logfile -
else
    # Development mode
    uvicorn app.main:app \
        --host 0.0.0.0 \
        --port 8000 \
        --reload
fi 