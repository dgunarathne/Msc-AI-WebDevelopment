from fastapi import APIRouter

from app.schemas.recommendation import RecommendRequest, RecommendResponse, StationRecommendation
from app.services.scoring import current_hour_and_day, score_station

router = APIRouter()


@router.post("/recommend/stations", response_model=RecommendResponse)
def recommend_stations(payload: RecommendRequest) -> RecommendResponse:
    hour_of_day = payload.hour_of_day if payload.hour_of_day is not None else current_hour_and_day()[0]
    day_of_week = payload.day_of_week if payload.day_of_week is not None else current_hour_and_day()[1]

    recommendations = []
    for station in payload.candidate_stations:
        score, distance_km, predicted_wait_minutes, reasons = score_station(
            station=station,
            rider_lat=payload.latitude,
            rider_lng=payload.longitude,
            vehicle=payload.vehicle,
            preferences=payload.preferences,
            hour_of_day=hour_of_day,
            day_of_week=day_of_week,
        )
        recommendations.append(
            StationRecommendation(
                charger_id=station.charger_id,
                distance_km=distance_km,
                predicted_wait_minutes=predicted_wait_minutes,
                score=score,
                reasons=reasons,
            )
        )

    recommendations.sort(key=lambda r: r.score, reverse=True)
    return RecommendResponse(recommendations=recommendations)
