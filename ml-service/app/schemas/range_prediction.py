from typing import Literal

from pydantic import BaseModel, Field


class RangeRequest(BaseModel):
    battery_capacity_kwh: float = Field(..., gt=0)
    current_soc_percent: float = Field(..., ge=0, le=100)
    efficiency_wh_per_km: float = Field(..., gt=0)
    trip_distance_km: float = Field(..., ge=0)
    avg_speed_kmh: float = Field(60.0, gt=0)
    temperature_c: float = 20.0
    elevation_gain_m: float = 0.0
    driving_style: Literal["eco", "normal", "aggressive"] = "normal"


class RangeResponse(BaseModel):
    usable_energy_kwh: float
    predicted_consumption_kwh: float
    predicted_remaining_range_km: float
    can_reach_destination: bool
    energy_margin_kwh: float
    recommend_charging_stop: bool
