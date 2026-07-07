from fastapi import APIRouter

from app.schemas.range_prediction import RangeRequest, RangeResponse
from app.services.model_registry import predict_actual_wh_per_km

router = APIRouter()

# Fraction of full battery capacity kept as a reserve buffer before recommending a stop.
SAFETY_BUFFER_FRACTION = 0.10


@router.post("/predict/range", response_model=RangeResponse)
def predict_range(payload: RangeRequest) -> RangeResponse:
    usable_energy_kwh = payload.battery_capacity_kwh * payload.current_soc_percent / 100.0

    actual_wh_per_km = predict_actual_wh_per_km(
        avg_speed_kmh=payload.avg_speed_kmh,
        temperature_c=payload.temperature_c,
        elevation_gain_per_km=(
            payload.elevation_gain_m / payload.trip_distance_km if payload.trip_distance_km > 0 else 0.0
        ),
        driving_style=payload.driving_style,
        baseline_efficiency_wh_per_km=payload.efficiency_wh_per_km,
    )

    predicted_consumption_kwh = actual_wh_per_km * payload.trip_distance_km / 1000.0
    predicted_remaining_range_km = (usable_energy_kwh * 1000.0) / actual_wh_per_km
    energy_margin_kwh = usable_energy_kwh - predicted_consumption_kwh
    safety_buffer_kwh = payload.battery_capacity_kwh * SAFETY_BUFFER_FRACTION

    can_reach_destination = energy_margin_kwh >= 0
    recommend_charging_stop = energy_margin_kwh < safety_buffer_kwh

    return RangeResponse(
        usable_energy_kwh=round(usable_energy_kwh, 2),
        predicted_consumption_kwh=round(predicted_consumption_kwh, 2),
        predicted_remaining_range_km=round(predicted_remaining_range_km, 1),
        can_reach_destination=can_reach_destination,
        energy_margin_kwh=round(energy_margin_kwh, 2),
        recommend_charging_stop=recommend_charging_stop,
    )
