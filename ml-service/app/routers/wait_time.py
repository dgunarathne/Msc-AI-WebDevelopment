from fastapi import APIRouter

from app.schemas.wait_time import WaitTimeRequest, WaitTimeResponse
from app.services.model_registry import predict_wait_minutes

router = APIRouter()


def _confidence_for(current_queue_length: int, active_sessions: int) -> str:
    sample_size_proxy = current_queue_length + active_sessions
    if sample_size_proxy == 0:
        return "high"
    if sample_size_proxy <= 3:
        return "medium"
    return "low"


@router.post("/predict/wait-time", response_model=WaitTimeResponse)
def predict_wait_time(payload: WaitTimeRequest) -> WaitTimeResponse:
    predicted_wait_minutes = predict_wait_minutes(
        number_of_connectors=payload.number_of_connectors,
        current_queue_length=payload.current_queue_length,
        active_sessions=payload.active_sessions,
        avg_session_minutes=payload.avg_session_minutes,
        hour_of_day=payload.hour_of_day,
        day_of_week=payload.day_of_week,
        is_dc_fast=payload.is_dc_fast,
    )
    return WaitTimeResponse(
        charger_id=payload.charger_id,
        predicted_wait_minutes=round(predicted_wait_minutes, 1),
        confidence=_confidence_for(payload.current_queue_length, payload.active_sessions),
    )
