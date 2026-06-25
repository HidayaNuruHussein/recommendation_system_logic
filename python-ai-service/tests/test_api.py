"""
Comprehensive tests for the Apriori recommendation service.
"""

import unittest
import json
from app import create_app
from app.apriori_model import AprioriModel


class TestAprioriModel(unittest.TestCase):
    """Unit tests for AprioriModel."""

    def setUp(self):
        """Set up test fixtures."""
        self.model = AprioriModel(
            min_support=0.3, min_confidence=0.5, min_lift=1.0
        )
        # Sample transactions (market basket data)
        self.transactions = [
            [1, 2, 3],
            [1, 2],
            [1, 3],
            [2, 3],
            [1, 2, 3],
            [2, 3, 4],
            [1, 4],
        ]

    def test_fit_model(self):
        """Test model fitting."""
        stats = self.model.fit(self.transactions)
        self.assertEqual(stats["status"], "success")
        self.assertGreater(stats["itemsets"], 0)
        self.assertEqual(stats["transactions"], 7)

    def test_recommend_single_product(self):
        """Test single product recommendation."""
        self.model.fit(self.transactions)
        recommendations = self.model.recommend(1, top_n=5)
        self.assertIsInstance(recommendations, list)
        if recommendations:
            self.assertIn("product_id", recommendations[0])
            self.assertIn("confidence", recommendations[0])
            self.assertIn("lift", recommendations[0])

    def test_recommend_multiple_products(self):
        """Test multiple product recommendations."""
        self.model.fit(self.transactions)
        recommendations = self.model.recommend_multiple([1, 2, 3], top_n=3)
        self.assertIsInstance(recommendations, dict)
        self.assertEqual(len(recommendations), 3)

    def test_empty_transactions_error(self):
        """Test that empty transactions raise error."""
        with self.assertRaises(ValueError):
            self.model.fit([])

    def test_get_stats(self):
        """Test model statistics."""
        self.model.fit(self.transactions)
        stats = self.model.get_stats()
        self.assertTrue(stats["fitted"])
        self.assertEqual(stats["transactions"], 7)
        self.assertGreater(stats["itemsets"], 0)

    def test_top_rules(self):
        """Test retrieving top rules."""
        self.model.fit(self.transactions)
        rules = self.model.get_top_rules(metric="confidence", top_n=5)
        self.assertIsInstance(rules, list)
        if rules:
            self.assertIn("antecedents", rules[0])
            self.assertIn("consequents", rules[0])
            self.assertIn("confidence", rules[0])


class TestAprioriAPI(unittest.TestCase):
    """Integration tests for the Flask API."""

    def setUp(self):
        """Set up Flask test client."""
        self.app = create_app()
        self.client = self.app.test_client()
        self.sample_transactions = [
            [1, 2, 3],
            [1, 2],
            [1, 3],
            [2, 3],
            [1, 2, 3],
            [2, 3, 4],
            [1, 4],
        ]

    def test_status_endpoint(self):
        """Test /api/status endpoint."""
        response = self.client.get("/api/status")
        self.assertEqual(response.status_code, 200)
        data = json.loads(response.data)
        self.assertEqual(data["status"], "ok")
        self.assertIn("model_stats", data)

    def test_fit_endpoint_success(self):
        """Test /api/fit endpoint with valid data."""
        response = self.client.post(
            "/api/fit",
            json={"transactions": self.sample_transactions},
            content_type="application/json",
        )
        self.assertEqual(response.status_code, 200)
        data = json.loads(response.data)
        self.assertEqual(data["status"], "success")
        self.assertGreater(data["itemsets"], 0)

    def test_fit_endpoint_invalid_input(self):
        """Test /api/fit with invalid input."""
        response = self.client.post(
            "/api/fit",
            json={"invalid": "data"},
            content_type="application/json",
        )
        self.assertEqual(response.status_code, 400)

    def test_recommend_endpoint_before_fit(self):
        """Test /api/recommend before fitting model."""
        response = self.client.post(
            "/api/recommend",
            json={"product_id": 1, "top_n": 10},
            content_type="application/json",
        )
        self.assertEqual(response.status_code, 400)

    def test_recommend_endpoint_after_fit(self):
        """Test /api/recommend after fitting model."""
        # First fit the model
        self.client.post(
            "/api/fit",
            json={"transactions": self.sample_transactions},
            content_type="application/json",
        )

        # Then request recommendations
        response = self.client.post(
            "/api/recommend",
            json={"product_id": 1, "top_n": 5},
            content_type="application/json",
        )
        self.assertEqual(response.status_code, 200)
        data = json.loads(response.data)
        self.assertEqual(data["product_id"], 1)
        self.assertIn("recommendations", data)

    def test_recommend_multiple_endpoint(self):
        """Test /api/recommend-multiple endpoint."""
        # Fit model
        self.client.post(
            "/api/fit",
            json={"transactions": self.sample_transactions},
            content_type="application/json",
        )

        # Request multiple recommendations
        response = self.client.post(
            "/api/recommend-multiple",
            json={"product_ids": [1, 2, 3], "top_n": 5},
            content_type="application/json",
        )
        self.assertEqual(response.status_code, 200)
        data = json.loads(response.data)
        self.assertIn("recommendations", data)
        self.assertEqual(len(data["recommendations"]), 3)

    def test_rules_endpoint(self):
        """Test /api/rules endpoint."""
        # Fit model
        self.client.post(
            "/api/fit",
            json={"transactions": self.sample_transactions},
            content_type="application/json",
        )

        # Get rules
        response = self.client.get("/api/rules?metric=confidence&top_n=10")
        self.assertEqual(response.status_code, 200)
        data = json.loads(response.data)
        self.assertEqual(data["metric"], "confidence")
        self.assertIn("rules", data)

    def test_rules_endpoint_invalid_metric(self):
        """Test /api/rules with invalid metric."""
        response = self.client.get("/api/rules?metric=invalid")
        self.assertEqual(response.status_code, 400)


if __name__ == "__main__":
    unittest.main()
