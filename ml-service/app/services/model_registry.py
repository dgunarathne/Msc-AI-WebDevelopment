import json
import os

import joblib

from app.config import settings

DRIVING_STYLE_ENCODING = {"eco": 0, "normal": 1, "aggressive": 2}

_wait_time_model = None
_range_model = None
_feature_metadata = {}


def load_models() -> None:
    """Load persisted models into memory. Called once at FastAPI startup."""
    global _wait_time_model, _range_model, _feature_metadata

    model_dir = settings.model_dir
    _wait_time_model = joblib.load(os.path.join(model_dir, "wait_time_model.pkl"))
    _range_model = joblib.load(os.path.join(model_dir, "range_model.pkl"))

    metadata_path = os.path.join(model_dir, "feature_metadata.json")
    with open(metadata_path) as f:
        _feature_metadata = json.load(f)


def _ensure_loaded():
    if _wait_time_model is None or _range_model is None:
        load_models()


def predict_wait_minutes(
    number_of_connectors: int,
    current_queue_length: int,
    active_sessions: int,
    avg_session_minutes: float,
    hour_of_day: int,
    day_of_week: int,
    is_dc_fast: bool,
) -> float:
    _ensure_loaded()
    is_weekend = 1 if day_of_week >= 5 else 0
    features = [
        [
            number_of_connectors,
            current_queue_length,
            active_sessions,
            avg_session_minutes,
            hour_of_day,
            day_of_week,
            is_weekend,
            int(is_dc_fast),
        ]
    ]
    prediction = _wait_time_model.predict(features)[0]
    return max(0.0, float(prediction))


def predict_actual_wh_per_km(
    avg_speed_kmh: float,
    temperature_c: float,
    elevation_gain_per_km: float,
    driving_style: str,
    baseline_efficiency_wh_per_km: float,
) -> float:
    _ensure_loaded()
    driving_style_code = DRIVING_STYLE_ENCODING.get(driving_style, 1)
    features = [
        [
            avg_speed_kmh,
            temperature_c,
            elevation_gain_per_km,
            driving_style_code,
            baseline_efficiency_wh_per_km,
        ]
    ]
    prediction = _range_model.predict(features)[0]
    return max(1.0, float(prediction))
