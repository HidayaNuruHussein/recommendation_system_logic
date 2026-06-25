"""Apriori Recommendation Service."""

from flask import Flask
from flask_cors import CORS

from app.config import Config


def create_app(config_class=Config):
    """Create and configure the Flask application."""
    app = Flask(__name__)
    app.config.from_object(config_class)

    # Enable CORS for internal services
    CORS(app, resources={r"/api/*": {"origins": app.config["CORS_ORIGINS"]}})

    # Register blueprints
    from app.routes import api_bp

    app.register_blueprint(api_bp)

    return app
