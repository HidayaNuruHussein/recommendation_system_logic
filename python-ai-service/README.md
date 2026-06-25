# Python Apriori Microservice

**Production-ready Apriori algorithm service for product recommendations in e-commerce.**

## Overview

This is a **standalone Python microservice** that implements the Apriori algorithm for market basket analysis and product recommendations. It runs independently from Laravel and exposes REST API endpoints.

**Key Features:**
- ✅ Market Basket Analysis using Apriori algorithm
- ✅ Association Rule Mining (frequent itemsets, confidence, lift)
- ✅ RESTful API for training and inference
- ✅ Optimized for large transaction datasets
- ✅ Built with Flask + mlxtend + pandas
- ✅ Comprehensive test coverage
- ✅ Production-safe (CORS, validation, error handling)

## Architecture

```
Laravel (Web Framework)
     ↓ HTTP
Python Service (Port 5001)
     ↓ In-Memory Model
Recommendations (JSON)
```

**Design Benefits:**
- Laravel untouched (no PHP code changes)
- Python service completely independent
- Horizontal scaling possible (multiple instances)
- Technology agnostic (can replace/upgrade Python logic)
- Easy to test and debug

## Setup & Installation

### Prerequisites
- Python 3.8+
- pip

### Installation

```bash
# Navigate to the service directory
cd python-ai-service

# Create virtual environment
python -m venv venv

# Activate virtual environment
# On Windows:
venv\Scripts\activate
# On macOS/Linux:
source venv/bin/activate

# Install dependencies
pip install -r requirements.txt

# Copy environment file
cp .env.example .env
```

### Configuration

Edit `.env` file to customize:
```env
FLASK_ENV=development           # Set to 'production' for deployment
MIN_SUPPORT=0.05                # Min support threshold (0-1)
MIN_CONFIDENCE=0.25             # Min confidence threshold (0-1)
MIN_LIFT=1.0                    # Min lift threshold
HOST=127.0.0.1                  # Listen address
PORT=5001                       # Listen port
```

### Running the Service

```bash
# Development mode (hot reload)
python app.py

# Or use Flask CLI
export FLASK_APP=app.py
flask run

# You should see:
# * Running on http://127.0.0.1:5001
```

## API Endpoints

### 1. Health Check

```http
GET /api/status
```

**Response:**
```json
{
    "status": "ok",
    "service": "Apriori Recommendation Engine",
    "model_stats": {
        "fitted": false,
        "transactions": 0,
        "itemsets": 0,
        "rules": 0
    }
}
```

### 2. Train Model

```http
POST /api/fit
Content-Type: application/json

{
    "transactions": [
        [1, 2, 3],
        [2, 4],
        [1, 4, 5],
        [1, 2, 4]
    ]
}
```

**Response:**
```json
{
    "status": "success",
    "transactions": 4,
    "itemsets": 12,
    "rules": 8,
    "min_support": 0.05,
    "min_confidence": 0.25,
    "min_lift": 1.0
}
```

### 3. Get Recommendations

```http
POST /api/recommend
Content-Type: application/json

{
    "product_id": 1,
    "top_n": 5
}
```

**Response:**
```json
{
    "product_id": 1,
    "recommendations": [
        {
            "product_id": 2,
            "confidence": 0.85,
            "lift": 1.25,
            "support": 0.15
        },
        {
            "product_id": 3,
            "confidence": 0.72,
            "lift": 1.10,
            "support": 0.12
        }
    ],
    "model_stats": {
        "fitted": true,
        "transactions": 4,
        "itemsets": 12,
        "rules": 8
    }
}
```

### 4. Get Multiple Recommendations

```http
POST /api/recommend-multiple
Content-Type: application/json

{
    "product_ids": [1, 2, 3],
    "top_n": 10
}
```

**Response:**
```json
{
    "recommendations": {
        "1": [{"product_id": 2, "confidence": 0.85, ...}],
        "2": [{"product_id": 1, "confidence": 0.92, ...}],
        "3": [{"product_id": 4, "confidence": 0.67, ...}]
    },
    "model_stats": {...}
}
```

### 5. Get Top Rules

```http
GET /api/rules?metric=confidence&top_n=20
```

**Query Parameters:**
- `metric`: `confidence`, `lift`, or `support` (default: `confidence`)
- `top_n`: number of rules to return (default: 20)

**Response:**
```json
{
    "metric": "confidence",
    "rules": [
        {
            "antecedents": [1],
            "consequents": [2],
            "support": 0.15,
            "confidence": 0.85,
            "lift": 1.25
        }
    ],
    "count": 1
}
```

## Testing

Run comprehensive test suite:

```bash
# Run all tests
python -m pytest tests/ -v

# Run with coverage
python -m pytest tests/ --cov=app --cov-report=html

# Run single test
python -m pytest tests/test_api.py::TestAprioriAPI::test_status_endpoint -v
```

## Performance Considerations

### For Large Datasets

1. **Minimum Support**: Increase min_support to reduce itemsets
   ```env
   MIN_SUPPORT=0.10  # Instead of 0.05
   ```

2. **Batch Processing**: Send transactions in chunks
   ```python
   # Fit with 10K transactions
   chunk_size = 1000
   for chunk in chunks(transactions, chunk_size):
       service.fit(chunk)
   ```

3. **Caching**: Cache model in Redis for production
   ```python
   import redis
   cache = redis.Redis()
   cache.set('apriori_model', pickle.dumps(model))
   ```

## Integration with Laravel

### Via HTTP Client (Recommended)

```php
// app/Services/RecommendationService.php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class PythonAprioriService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('PYTHON_AI_SERVICE_URL', 'http://127.0.0.1:5001');
    }

    /**
     * Train model with transaction data from Laravel database.
     */
    public function trainModel(array $transactions): array
    {
        $response = Http::post("{$this->baseUrl}/api/fit", [
            'transactions' => $transactions,
        ]);

        if ($response->failed()) {
            throw new \Exception("Python service error: " . $response->body());
        }

        return $response->json();
    }

    /**
     * Get recommendations for a product.
     */
    public function getRecommendations(int $productId, int $topN = 10): array
    {
        $response = Http::post("{$this->baseUrl}/api/recommend", [
            'product_id' => $productId,
            'top_n' => $topN,
        ]);

        if ($response->failed()) {
            return [];
        }

        return $response->json()['recommendations'] ?? [];
    }

    /**
     * Check if service is available.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(2)->get("{$this->baseUrl}/api/status");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

### Usage in Controller

```php
// In a controller or route
use App\Services\PythonAprioriService;

$service = new PythonAprioriService();

// Check health
if (!$service->isHealthy()) {
    return response()->json(['error' => 'AI service unavailable'], 503);
}

// Get recommendations
$recommendations = $service->getRecommendations(product_id: 5, topN: 10);
```

## Deployment

### Docker

```dockerfile
FROM python:3.11-slim

WORKDIR /app

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app/ ./app
COPY app.py .

EXPOSE 5001

ENV FLASK_ENV=production
CMD ["gunicorn", "--bind", "0.0.0.0:5001", "app:app"]
```

Run:
```bash
docker build -t apriori-service .
docker run -p 5001:5001 -e MIN_SUPPORT=0.05 apriori-service
```

### Production Checklist

- [ ] Set `FLASK_ENV=production`
- [ ] Use strong `SECRET_KEY`
- [ ] Set `API_KEY` for authentication
- [ ] Configure `CORS_ORIGINS` properly
- [ ] Run with Gunicorn/uWSGI (not Flask dev server)
- [ ] Use reverse proxy (Nginx)
- [ ] Enable HTTPS/TLS
- [ ] Monitor service health (logging, metrics)
- [ ] Set up automatic model retraining (cron job)

## Troubleshooting

### Issue: "Model not fitted"
**Solution:** Call `/api/fit` first with transaction data

### Issue: No recommendations returned
**Solution:** Check min_support and min_confidence thresholds are not too high

### Issue: High latency with large transactions
**Solution:** Increase MIN_SUPPORT value to reduce itemset generation time

### Issue: Service unreachable from Laravel
**Solution:** Check firewall rules and ensure Python service is running on correct port

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for development guidelines.

## License

MIT License - See LICENSE file for details.

## Support

For issues, questions, or contributions:
- Create an issue on GitHub
- Contact: support@example.com
