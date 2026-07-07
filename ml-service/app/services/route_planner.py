"""
Greedy single-pass route planner: walks from origin to destination, and
whenever projected SoC would drop below the safety margin, inserts a
charging stop at the best reachable candidate station (scored the same way
as /recommend/stations, biased toward stations that make progress toward
the destination). This is intentionally not a full multi-stop graph
optimizer (e.g. Dijkstra over a station graph) — documented as a known MVP
simplification; see ml-service/README.md.
"""
from typing import List, Optional, Tuple

from app.schemas.recommendation import CandidateStation, Preferences, VehicleContext
from app.services.model_registry import predict_actual_wh_per_km
from app.services.osrm_client import get_route_with_fallback
from app.services.scoring import haversine_km

DEFAULT_CHARGE_RATE_KW = 22.0
MAX_STOPS = 5


def _consumption_wh_per_km(vehicle: VehicleContext, avg_speed_kmh: float) -> float:
    return predict_actual_wh_per_km(
        avg_speed_kmh=avg_speed_kmh,
        temperature_c=20.0,
        elevation_gain_per_km=0.0,
        driving_style="normal",
        baseline_efficiency_wh_per_km=vehicle.efficiency_wh_per_km,
    )


def _max_range_km(usable_energy_kwh: float, wh_per_km: float) -> float:
    return (usable_energy_kwh * 1000.0) / wh_per_km


def _pick_best_stop(
    current_lat: float,
    current_lng: float,
    dest_lat: float,
    dest_lng: float,
    max_reachable_km: float,
    candidates: List[CandidateStation],
    vehicle: VehicleContext,
    used_charger_ids: set,
) -> Optional[Tuple[CandidateStation, float]]:
    """Returns (station, leg_distance_km) for the best reachable, forward-progress station."""
    dist_current_to_dest = haversine_km(current_lat, current_lng, dest_lat, dest_lng)
    best = None
    best_score = -1.0

    for station in candidates:
        if station.charger_id in used_charger_ids:
            continue
        leg_km = haversine_km(current_lat, current_lng, station.latitude, station.longitude)
        if leg_km > max_reachable_km:
            continue
        dist_station_to_dest = haversine_km(station.latitude, station.longitude, dest_lat, dest_lng)
        if dist_station_to_dest >= dist_current_to_dest:
            continue  # station doesn't make progress toward destination

        progress_score = (dist_current_to_dest - dist_station_to_dest) / max(dist_current_to_dest, 1e-6)
        connector_bonus = 1.0 if station.connector_type == vehicle.connector_type else 0.5
        queue_penalty = 1.0 / (1.0 + station.current_queue_length)
        score = progress_score * connector_bonus * queue_penalty

        if score > best_score:
            best_score = score
            best = (station, leg_km)

    return best


def plan_route(
    origin_lat: float,
    origin_lng: float,
    dest_lat: float,
    dest_lng: float,
    vehicle: VehicleContext,
    candidate_stations: List[CandidateStation],
    safety_margin_percent: float,
    target_soc_after_charge_percent: float,
) -> dict:
    base_route = get_route_with_fallback(origin_lat, origin_lng, dest_lat, dest_lng)
    total_distance_km = base_route["distance_km"]
    total_duration_minutes = base_route["duration_minutes"]
    avg_speed_kmh = (total_distance_km / (total_duration_minutes / 60.0)) if total_duration_minutes > 0 else 60.0

    wh_per_km = _consumption_wh_per_km(vehicle, avg_speed_kmh)
    safety_margin_kwh = vehicle.battery_capacity_kwh * safety_margin_percent / 100.0

    current_lat, current_lng = origin_lat, origin_lng
    current_soc_percent = vehicle.current_soc_percent
    stops = []
    used_charger_ids = set()
    feasible = True

    for _ in range(MAX_STOPS):
        usable_energy_kwh = vehicle.battery_capacity_kwh * current_soc_percent / 100.0
        remaining_to_dest_km = haversine_km(current_lat, current_lng, dest_lat, dest_lng)
        energy_needed_kwh = wh_per_km * remaining_to_dest_km / 1000.0
        margin_kwh = usable_energy_kwh - energy_needed_kwh

        if margin_kwh >= safety_margin_kwh:
            break  # can reach destination safely from here

        max_reachable_km = _max_range_km(max(0.0, usable_energy_kwh - safety_margin_kwh), wh_per_km)
        pick = _pick_best_stop(
            current_lat, current_lng, dest_lat, dest_lng, max_reachable_km,
            candidate_stations, vehicle, used_charger_ids,
        )
        if pick is None:
            feasible = False
            break

        station, leg_km = pick
        used_charger_ids.add(station.charger_id)
        arrival_energy_kwh = usable_energy_kwh - (wh_per_km * leg_km / 1000.0)
        arrival_soc_percent = max(0.0, (arrival_energy_kwh / vehicle.battery_capacity_kwh) * 100.0)

        charge_rate_kw = DEFAULT_CHARGE_RATE_KW
        energy_to_add_kwh = max(
            0.0, (target_soc_after_charge_percent - arrival_soc_percent) / 100.0 * vehicle.battery_capacity_kwh
        )
        recommended_charge_minutes = (energy_to_add_kwh / charge_rate_kw) * 60.0

        stops.append(
            {
                "charger_id": station.charger_id,
                "arrival_soc_percent": round(arrival_soc_percent, 1),
                "leg_distance_km": round(leg_km, 2),
                "recommended_charge_minutes": round(recommended_charge_minutes, 1),
                "station": station,
            }
        )

        current_lat, current_lng = station.latitude, station.longitude
        current_soc_percent = target_soc_after_charge_percent

    return {
        "total_distance_km": round(total_distance_km, 2),
        "total_duration_minutes": round(total_duration_minutes, 1),
        "charging_stops_required": len(stops) > 0,
        "stops": stops,
        "route_geometry": base_route.get("geometry"),
        "feasible": feasible,
    }
