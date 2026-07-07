from typing import List, Optional

from pydantic import BaseModel, Field


class VehicleContext(BaseModel):
    connector_type: str = "Type2"
    battery_capacity_kwh: float = 60.0
    current_soc_percent: float = 60.0
    efficiency_wh_per_km: float = 160.0


class Preferences(BaseModel):
    max_price_per_kwh: Optional[float] = None
    prefer_fast_charging: bool = False
    max_distance_km: float = 25.0


class CandidateStation(BaseModel):
    charger_id: int
    latitude: float
    longitude: float
    connector_type: str
    price_per_kwh: float
    power_output: float
    current_queue_length: int = 0
    number_of_connectors: int = 1
    active_sessions: int = 0
    avg_session_minutes: float = 45.0


class RecommendRequest(BaseModel):
    latitude: float
    longitude: float
    vehicle: VehicleContext
    preferences: Preferences = Preferences()
    candidate_stations: List[CandidateStation] = Field(default_factory=list)
    hour_of_day: Optional[int] = None
    day_of_week: Optional[int] = None


class StationRecommendation(BaseModel):
    charger_id: int
    distance_km: float
    predicted_wait_minutes: float
    score: float
    reasons: List[str]


class RecommendResponse(BaseModel):
    recommendations: List[StationRecommendation]
