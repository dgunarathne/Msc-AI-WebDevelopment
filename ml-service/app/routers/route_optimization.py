from fastapi import APIRouter

from app.schemas.route_optimization import RouteRequest, RouteResponse
from app.services.route_planner import plan_route

router = APIRouter()


@router.post("/route/plan", response_model=RouteResponse)
def route_plan(payload: RouteRequest) -> RouteResponse:
    result = plan_route(
        origin_lat=payload.origin.latitude,
        origin_lng=payload.origin.longitude,
        dest_lat=payload.destination.latitude,
        dest_lng=payload.destination.longitude,
        vehicle=payload.vehicle,
        candidate_stations=payload.candidate_stations,
        safety_margin_percent=payload.safety_margin_percent,
        target_soc_after_charge_percent=payload.target_soc_after_charge_percent,
    )
    return RouteResponse(**result)
