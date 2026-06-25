"""API routes for the Apriori recommendation service."""

from flask import Blueprint, request, jsonify
from pydantic import BaseModel, ValidationError
from typing import List, Optional

from app.apriori_model import AprioriModel
from app.config import Config

api_bp = Blueprint("api", __name__, url_prefix="/api")

# Global model instance (in production, use Redis or database)
model = AprioriModel(
    min_support=Config.MIN_SUPPORT,
    min_confidence=Config.MIN_CONFIDENCE,
    min_lift=Config.MIN_LIFT,
)


# Pydantic validators
class TransactionData(BaseModel):
    """Request schema for fitting the model."""

    transactions: List[List[int]]

    class Config:
        """Pydantic config."""

        str_strip_whitespace = True


class RecommendRequest(BaseModel):
    """Request schema for recommendations."""

    product_id: int
    top_n: Optional[int] = 10

    class Config:
        """Pydantic config."""

        str_strip_whitespace = True


class RecommendMultipleRequest(BaseModel):
    """Request schema for multiple recommendations."""

    product_ids: List[int]
    top_n: Optional[int] = 10

    class Config:
        """Pydantic config."""

        str_strip_whitespace = True


def verify_api_key():
    """Verify API key if configured."""
    if not Config.API_KEY:
        return True  # No API key required in dev

    auth_header = request.headers.get("Authorization", "")
    if not auth_header.startswith("Bearer "):
        return False

    token = auth_header[7:]
    return token == Config.API_KEY


@api_bp.route("/status", methods=["GET"])
def status():
    """Health check endpoint."""
    return jsonify(
        {
            "status": "ok",
            "service": "Apriori Recommendation Engine",
            "model_stats": model.get_stats(),
        }
    )


@api_bp.route("/fit", methods=["POST"])
def fit():
    """
    Train the Apriori model on transaction data.

    Request JSON:
    {
        "transactions": [
            [1, 2, 3],
            [2, 4],
            [1, 4, 5]
        ]
    }
    """
    try:
        payload = TransactionData(**request.get_json())
    except ValidationError as e:
        return jsonify({"error": "Invalid input", "details": e.errors()}), 400
    except Exception as e:
        return jsonify({"error": str(e)}), 400

    try:
        stats = model.fit(payload.transactions)
        return jsonify(stats)
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@api_bp.route("/recommend", methods=["POST"])
def recommend():
    """
    Get product recommendations.

    Request JSON:
    {
        "product_id": 5,
        "top_n": 10
    }
    """
    try:
        payload = RecommendRequest(**request.get_json())
    except ValidationError as e:
        return jsonify({"error": "Invalid input", "details": e.errors()}), 400
    except Exception as e:
        return jsonify({"error": str(e)}), 400

    if model.frequent_itemsets is None:
        return (
            jsonify({"error": "Model not fitted. Call /api/fit first."}),
            400,
        )

    try:
        recommendations = model.recommend(payload.product_id, payload.top_n)
        return jsonify(
            {
                "product_id": payload.product_id,
                "recommendations": recommendations,
                "model_stats": model.get_stats(),
            }
        )
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@api_bp.route("/recommend-multiple", methods=["POST"])
def recommend_multiple():
    """
    Get recommendations for multiple products.

    Request JSON:
    {
        "product_ids": [1, 2, 3, 4],
        "top_n": 5
    }
    """
    try:
        payload = RecommendMultipleRequest(**request.get_json())
    except ValidationError as e:
        return jsonify({"error": "Invalid input", "details": e.errors()}), 400
    except Exception as e:
        return jsonify({"error": str(e)}), 400

    if model.frequent_itemsets is None:
        return (
            jsonify({"error": "Model not fitted. Call /api/fit first."}),
            400,
        )

    try:
        recommendations = model.recommend_multiple(payload.product_ids, payload.top_n)
        return jsonify(
            {
                "recommendations": recommendations,
                "model_stats": model.get_stats(),
            }
        )
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@api_bp.route("/rules", methods=["GET"])
def get_rules():
    """
    Get top association rules.

    Query params:
    - metric: 'confidence', 'lift', or 'support' (default: 'confidence')
    - top_n: number of rules (default: 20)
    """
    metric = request.args.get("metric", "confidence")
    top_n = int(request.args.get("top_n", 20))

    if metric not in ["confidence", "lift", "support"]:
        return jsonify({"error": "Invalid metric. Use confidence, lift, or support."}), 400

    if model.rules is None:
        return jsonify({"error": "Model not fitted. Call /api/fit first."}), 400

    try:
        rules = model.get_top_rules(metric, top_n)
        return jsonify({"metric": metric, "rules": rules, "count": len(rules)})
    except Exception as e:
        return jsonify({"error": str(e)}), 500
