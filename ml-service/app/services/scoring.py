"""
Weighted multi-criteria scoring for station recommendation.

No separate learned ranking model is used here: there is no ground-truth
"good recommendation" label to train against (see ml-service/README.md).
Instead this combines distance, predicted wait time, price fit, and
connector/charging-speed match into an explainable weighted score in [0, 1].
"""
import math
from datetime import datetime
from typing import List, Tuple

from app.schemas.recommendation import CandidateStation, Preferences, VehicleContext
from app.services.model_registry import predict_wait_minutes

WEIGHTS = {
    "distance": 0.35,
    "wait": 0.30,
    "price": 0.20,
    "connector_match": 0.10,
    "fast_charge_match": 0.05,
}


def haversine_km(lat1: float, lng1: float, lat2: float, lng2: float) -> float:
    R = 6371.0
    lat1r, lng1r, lat2r, lng2r = map(math.radians, [lat1, lng1, lat2, lng2])
    dlat = lat2r - lat1r
    dlng = lng2r - lng1r
    a = math.sin(dlat / 2) ** 2 + math.cos(lat1r) * math.cos(lat2r) * math.sin(dlng / 2) ** 2
    return R * 2 * math.asin(math.sqrt(a))


def _normalized_inverse(value: float, scale: float) -> float:
    """Maps 0 -> 1.0 and +inf -> 0.0, with `scale` controlling how fast it decays."""
    return 1.0 / (1.0 + value / scale) if scale > 0 else 0.0


def score_station(
    station: CandidateStation,
    rider_lat: float,
    rider_lng: float,
    vehicle: VehicleContext,
    preferences: Preferences,
    hour_of_day: int,
    day_of_week: int,
) -> Tuple[float, float, float, List[str]]:
    """Returns (score, distance_km, predicted_wait_minutes, reasons)."""
    reasons = []

    distance_km = haversine_km(rider_lat, rider_lng, station.latitude, station.longitude)
    distance_score = _normalized_inverse(distance_km, scale=preferences.max_distance_km or 25.0)
    if distance_km <= (preferences.max_distance_km or 25.0) * 0.3:
        reasons.append("close by")

    is_dc_fast = station.power_output >= 22
    predicted_wait_minutes = predict_wait_minutes(
        number_of_connectors=station.number_of_connectors,
        current_queue_length=station.current_queue_length,
        active_sessions=station.active_sessions,
        avg_session_minutes=station.avg_session_minutes,
        hour_of_day=hour_of_day,
        day_of_week=day_of_week,
        is_dc_fast=is_dc_fast,
    )
    wait_score = _normalized_inverse(predicted_wait_minutes, scale=20.0)
    if predicted_wait_minutes <= 5:
        reasons.append("low wait")

    if preferences.max_price_per_kwh:
        price_score = _normalized_inverse(
            max(0.0, station.price_per_kwh - preferences.max_price_per_kwh), scale=preferences.max_price_per_kwh
        )
        if station.price_per_kwh <= preferences.max_price_per_kwh:
            reasons.append("within budget")
    else:
        price_score = 0.5

    connector_match = 1.0 if station.connector_type == vehicle.connector_type else 0.2
    if connector_match == 1.0:
        reasons.append("matches connector")

    fast_charge_match = 1.0 if (not preferences.prefer_fast_charging or is_dc_fast) else 0.3
    if preferences.prefer_fast_charging and is_dc_fast:
        reasons.append("fast charging")

    score = (
        WEIGHTS["distance"] * distance_score
        + WEIGHTS["wait"] * wait_score
        + WEIGHTS["price"] * price_score
        + WEIGHTS["connector_match"] * connector_match
        + WEIGHTS["fast_charge_match"] * fast_charge_match
    )

    return round(score, 4), round(distance_km, 2), round(predicted_wait_minutes, 1), reasons


def current_hour_and_day() -> Tuple[int, int]:
    now = datetime.now()
    return now.hour, now.weekday()
