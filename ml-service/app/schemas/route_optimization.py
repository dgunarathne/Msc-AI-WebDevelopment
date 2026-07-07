from typing import List, Optional

from pydantic import BaseModel

from app.schemas.recommendation import CandidateStation, VehicleContext


class LatLng(BaseModel):
    latitude: float
    longitude: float


class RouteRequest(BaseModel):
    origin: LatLng
    destination: LatLng
    vehicle: VehicleContext
    candidate_stations: List[CandidateStation] = []
    safety_margin_percent: float = 15.0
    target_soc_after_charge_percent: float = 80.0


class RouteStop(BaseModel):
    charger_id: int
    arrival_soc_percent: float
    leg_distance_km: float
    recommended_charge_minutes: float
    station: CandidateStation


class RouteResponse(BaseModel):
    total_distance_km: float
    total_duration_minutes: float
    charging_stops_required: bool
    stops: List[RouteStop]
    route_geometry: Optional[dict] = None
    feasible: bool
