"""Configuration for the Apriori recommendation service."""

import os
from dotenv import load_dotenv

load_dotenv()


class Config:
    """Base configuration."""

    # Flask
    FLASK_ENV = os.getenv("FLASK_ENV", "development")
    DEBUG = FLASK_ENV == "development"
    SECRET_KEY = os.getenv("SECRET_KEY", "dev-secret-key-change-in-production")

    # API
    HOST = os.getenv("HOST", "127.0.0.1")
    PORT = int(os.getenv("PORT", 5001))

    # Apriori parameters
    MIN_SUPPORT = float(os.getenv("MIN_SUPPORT", 0.05))
    MIN_CONFIDENCE = float(os.getenv("MIN_CONFIDENCE", 0.25))
    MIN_LIFT = float(os.getenv("MIN_LIFT", 1.0))

    # API key (optional, for production)
    API_KEY = os.getenv("API_KEY", "")

    # Laravel integration
    LARAVEL_BASE_URL = os.getenv("LARAVEL_BASE_URL", "http://localhost:8000")
    LARAVEL_API_TOKEN = os.getenv("LARAVEL_API_TOKEN", "")

    # CORS
    CORS_ORIGINS = os.getenv("CORS_ORIGINS", "localhost,127.0.0.1").split(",")
