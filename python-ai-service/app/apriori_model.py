"""
Association Rule Mining using Apriori Algorithm.
Generates frequent itemsets and association rules from transaction data.
"""

from typing import List, Dict, Tuple, Any
import pandas as pd
from mlxtend.frequent_patterns import apriori, association_rules
from mlxtend.preprocessing import TransactionEncoder
import numpy as np


class AprioriModel:
    """Apriori algorithm for market basket analysis."""

    def __init__(
        self,
        min_support: float = 0.05,
        min_confidence: float = 0.25,
        min_lift: float = 1.0,
    ):
        """
        Initialize the Apriori model.

        Args:
            min_support: Minimum support threshold (0-1)
            min_confidence: Minimum confidence threshold (0-1)
            min_lift: Minimum lift threshold for rules
        """
        self.min_support = min_support
        self.min_confidence = min_confidence
        self.min_lift = min_lift
        self.frequent_itemsets = None
        self.rules = None
        self.transaction_count = 0

    def fit(self, transactions: List[List[int]]) -> Dict[str, Any]:
        """
        Fit the model on transaction data.

        Args:
            transactions: List of transactions, each containing product IDs.
                         E.g., [[1, 2, 3], [2, 4], [1, 4, 5]]

        Returns:
            Dictionary with model stats (itemsets count, rules count, etc.)
        """
        if not transactions or len(transactions) == 0:
            raise ValueError("Transactions list cannot be empty")

        self.transaction_count = len(transactions)

        # Convert transactions to one-hot encoded DataFrame
        te = TransactionEncoder()
        te_ary = te.fit(transactions).transform(transactions)
        df = pd.DataFrame(te_ary, columns=te.columns_)

        # Generate frequent itemsets
        self.frequent_itemsets = apriori(
            df,
            min_support=self.min_support,
            use_colnames=True,
            max_len=None,
        )

        if len(self.frequent_itemsets) == 0:
            self.rules = pd.DataFrame(
                columns=["antecedents", "consequents", "support", "confidence", "lift"]
            )
            return {
                "status": "success",
                "transactions": self.transaction_count,
                "itemsets": 0,
                "rules": 0,
                "min_support": self.min_support,
            }

        # Generate association rules
        self.rules = association_rules(
            self.frequent_itemsets,
            metric="confidence",
            min_threshold=self.min_confidence,
        )

        # Filter by lift
        if len(self.rules) > 0:
            self.rules = self.rules[self.rules["lift"] >= self.min_lift]

        return {
            "status": "success",
            "transactions": self.transaction_count,
            "itemsets": len(self.frequent_itemsets),
            "rules": len(self.rules),
            "min_support": self.min_support,
            "min_confidence": self.min_confidence,
            "min_lift": self.min_lift,
        }

    def recommend(
        self, product_id: int, top_n: int = 10
    ) -> List[Dict[str, Any]]:
        """
        Get top N product recommendations for a given product.

        Args:
            product_id: The product to get recommendations for
            top_n: Number of recommendations to return

        Returns:
            List of dicts with recommended product_id, confidence, and lift
        """
        if self.rules is None or len(self.rules) == 0:
            return []

        # Find rules where product_id is in antecedents
        recommendations = []

        for _, rule in self.rules.iterrows():
            antecedents = frozenset(rule["antecedents"])
            consequents = frozenset(rule["consequents"])

            # Rule: product_id → consequent
            if product_id in antecedents and len(consequents) == 1:
                rec_product_id = int(list(consequents)[0])
                recommendations.append(
                    {
                        "product_id": rec_product_id,
                        "confidence": round(float(rule["confidence"]), 4),
                        "lift": round(float(rule["lift"]), 4),
                        "support": round(float(rule["support"]), 4),
                    }
                )

        # Sort by confidence, then lift
        recommendations.sort(
            key=lambda x: (x["confidence"], x["lift"]), reverse=True
        )

        return recommendations[:top_n]

    def recommend_multiple(
        self, product_ids: List[int], top_n: int = 10
    ) -> Dict[int, List[Dict[str, Any]]]:
        """
        Get recommendations for multiple products.

        Args:
            product_ids: List of product IDs
            top_n: Number of recommendations per product

        Returns:
            Dict mapping product_id to list of recommendations
        """
        return {pid: self.recommend(pid, top_n) for pid in product_ids}

    def get_stats(self) -> Dict[str, Any]:
        """Get model statistics."""
        return {
            "fitted": self.frequent_itemsets is not None,
            "transactions": self.transaction_count,
            "itemsets": len(self.frequent_itemsets) if self.frequent_itemsets is not None else 0,
            "rules": len(self.rules) if self.rules is not None else 0,
        }

    def get_top_rules(self, metric: str = "confidence", top_n: int = 20):
        """
        Get top association rules sorted by metric.

        Args:
            metric: 'confidence', 'lift', or 'support'
            top_n: Number of rules to return

        Returns:
            List of rule dicts
        """
        if self.rules is None or len(self.rules) == 0:
            return []

        sorted_rules = self.rules.sort_values(by=metric, ascending=False).head(top_n)

        result = []
        for _, rule in sorted_rules.iterrows():
            antecedents = sorted(list(rule["antecedents"]))
            consequents = sorted(list(rule["consequents"]))
            result.append(
                {
                    "antecedents": [int(x) for x in antecedents],
                    "consequents": [int(x) for x in consequents],
                    "support": round(float(rule["support"]), 4),
                    "confidence": round(float(rule["confidence"]), 4),
                    "lift": round(float(rule["lift"]), 4),
                }
            )

        return result
