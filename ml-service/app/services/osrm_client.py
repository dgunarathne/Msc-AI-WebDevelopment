from typing import Optional

import httpx

from app.config import settings


class OsrmError(Exception):
    pass


def get_route(
    origin_lat: float,
    origin_lng: float,
    dest_lat: float,
    dest_lng: float,
) -> dict:
    """Call the OSRM /route service and return distance/duration/geometry.

    Uses the public OSRM demo server by default (no API key, no SLA — see
    ml-service/README.md for self-hosting notes). Coordinates are (lng, lat)
    per OSRM convention.
    """
    url = (
        f"{settings.osrm_base_url}/route/v1/driving/"
        f"{origin_lng},{origin_lat};{dest_lng},{dest_lat}"
    )
    params = {"overview": "full", "geometries": "geojson"}

    try:
        response = httpx.get(url, params=params, timeout=10.0)
        response.raise_for_status()
    except httpx.HTTPError as exc:
        raise OsrmError(f"OSRM request failed: {exc}") from exc

    data = response.json()
    if data.get("code") != "Ok" or not data.get("routes"):
        raise OsrmError(f"OSRM returned no route: {data.get('code')}")

    route = data["routes"][0]
    return {
        "distance_km": route["distance"] / 1000.0,
        "duration_minutes": route["duration"] / 60.0,
        "geometry": route.get("geometry"),
    }


def straight_line_fallback(origin_lat: float, origin_lng: float, dest_lat: float, dest_lng: float) -> dict:
    """Haversine fallback used when OSRM is unreachable, so route planning degrades gracefully."""
    import math

    R = 6371.0
    lat1, lng1, lat2, lng2 = map(math.radians, [origin_lat, origin_lng, dest_lat, dest_lng])
    dlat = lat2 - lat1
    dlng = lng2 - lng1
    a = math.sin(dlat / 2) ** 2 + math.cos(lat1) * math.cos(lat2) * math.sin(dlng / 2) ** 2
    distance_km = R * 2 * math.asin(math.sqrt(a))
    assumed_avg_speed_kmh = 60.0
    return {
        "distance_km": distance_km,
        "duration_minutes": (distance_km / assumed_avg_speed_kmh) * 60.0,
        "geometry": None,
    }


def get_route_with_fallback(
    origin_lat: float, origin_lng: float, dest_lat: float, dest_lng: float
) -> dict:
    try:
        return get_route(origin_lat, origin_lng, dest_lat, dest_lng)
    except OsrmError:
        return straight_line_fallback(origin_lat, origin_lng, dest_lat, dest_lng)
