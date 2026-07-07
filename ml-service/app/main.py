from contextlib import asynccontextmanager

from fastapi import FastAPI

from app.routers import range_prediction, recommendation, route_optimization, wait_time
from app.services.model_registry import load_models


@asynccontextmanager
async def lifespan(app: FastAPI):
    load_models()
    yield


app = FastAPI(title="EVCircle ML Service", lifespan=lifespan)

app.include_router(wait_time.router, tags=["wait-time"])
app.include_router(range_prediction.router, tags=["range"])
app.include_router(recommendation.router, tags=["recommendation"])
app.include_router(route_optimization.router, tags=["route"])


@app.get("/health")
def health():
    return {"status": "ok"}
