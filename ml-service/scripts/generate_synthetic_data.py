"""
SYNTHETIC DATA GENERATOR — NOT REAL TELEMETRY.

EVCircle currently captures no charging-session telemetry (no real queue
lengths, no real consumption logs). These two datasets are generated from
domain-assumption formulas (a simplified M/M/c-style queueing approximation
for wait time, and known EV-physics approximations for consumption) plus
Gaussian noise, so the models learn a plausible nonlinear relationship rather
than pure noise. They exist to let the ML service function end-to-end today;
they should be replaced by models retrained on real `charging_sessions` rows
once enough are collected in production.
"""
import os

import numpy as np
import pandas as pd

RNG = np.random.default_rng(42)
DATA_DIR = os.path.join(os.path.dirname(__file__), "..", "data")


def demand_intensity(hour: float) -> float:
    """Bimodal demand curve peaking at ~8am and ~6pm."""
    morning_peak = np.exp(-((hour - 8) ** 2) / (2 * 2.5**2))
    evening_peak = np.exp(-((hour - 18) ** 2) / (2 * 2.5**2))
    return 0.15 + 0.85 * np.maximum(morning_peak, evening_peak)


def generate_wait_time_data(n_rows: int = 8000) -> pd.DataFrame:
    number_of_connectors = RNG.integers(1, 7, size=n_rows)
    hour_of_day = RNG.integers(0, 24, size=n_rows)
    day_of_week = RNG.integers(0, 7, size=n_rows)
    is_weekend = (day_of_week >= 5).astype(int)
    is_dc_fast = RNG.integers(0, 2, size=n_rows)
    avg_session_minutes = np.where(
        is_dc_fast == 1,
        RNG.normal(25, 5, size=n_rows),
        RNG.normal(50, 12, size=n_rows),
    ).clip(10, 120)

    intensity = demand_intensity(hour_of_day) * np.where(is_weekend == 1, 0.8, 1.0)
    # Expected number of vehicles wanting to charge right now, scaled by connectors.
    expected_demand = intensity * number_of_connectors * RNG.uniform(0.8, 2.2, size=n_rows)
    active_sessions = np.minimum(number_of_connectors, np.round(expected_demand)).astype(int)
    current_queue_length = np.maximum(
        0, np.round(expected_demand - number_of_connectors + RNG.normal(0, 1, size=n_rows))
    ).astype(int)

    # M/M/c-style approximation: wait ~ (excess demand over free connectors) * avg service time / connectors.
    free_connectors = np.maximum(1, number_of_connectors - active_sessions)
    base_wait = np.maximum(0, current_queue_length) * avg_session_minutes / free_connectors
    noise = RNG.normal(0, base_wait * 0.15 + 1.5, size=n_rows)
    wait_minutes = np.maximum(0, base_wait + noise)

    return pd.DataFrame(
        {
            "number_of_connectors": number_of_connectors,
            "current_queue_length": current_queue_length,
            "active_sessions": active_sessions,
            "avg_session_minutes": avg_session_minutes,
            "hour_of_day": hour_of_day,
            "day_of_week": day_of_week,
            "is_weekend": is_weekend,
            "is_dc_fast": is_dc_fast,
            "wait_minutes": wait_minutes,
        }
    )


def generate_range_data(n_rows: int = 8000) -> pd.DataFrame:
    avg_speed_kmh = RNG.uniform(20, 120, size=n_rows)
    temperature_c = RNG.uniform(-5, 40, size=n_rows)
    elevation_gain_per_km = RNG.uniform(-50, 100, size=n_rows)
    driving_style_code = RNG.integers(0, 3, size=n_rows)  # 0=eco,1=normal,2=aggressive
    baseline_efficiency_wh_per_km = RNG.uniform(120, 220, size=n_rows)

    # Aerodynamic drag penalty grows quadratically above ~80 km/h.
    speed_excess = np.maximum(0, avg_speed_kmh - 80)
    drag_multiplier = 1.0 + 0.00018 * speed_excess**2

    # Cold-weather HVAC/battery-heating penalty below 10C, mild efficiency gain in warm range.
    cold_penalty = np.where(temperature_c < 10, (10 - temperature_c) * 0.012, 0.0)
    hot_penalty = np.where(temperature_c > 30, (temperature_c - 30) * 0.004, 0.0)
    temp_multiplier = 1.0 + cold_penalty + hot_penalty

    # Elevation gain increases consumption roughly linearly.
    elevation_multiplier = 1.0 + np.maximum(0, elevation_gain_per_km) * 0.003

    style_multiplier = np.select(
        [driving_style_code == 0, driving_style_code == 1, driving_style_code == 2],
        [0.9, 1.0, 1.2],
    )

    actual_wh_per_km = (
        baseline_efficiency_wh_per_km
        * drag_multiplier
        * temp_multiplier
        * elevation_multiplier
        * style_multiplier
    )
    noise = RNG.normal(0, actual_wh_per_km * 0.04, size=n_rows)
    actual_wh_per_km = np.maximum(60, actual_wh_per_km + noise)

    driving_style = np.select(
        [driving_style_code == 0, driving_style_code == 1, driving_style_code == 2],
        ["eco", "normal", "aggressive"],
    )

    return pd.DataFrame(
        {
            "avg_speed_kmh": avg_speed_kmh,
            "temperature_c": temperature_c,
            "elevation_gain_per_km": elevation_gain_per_km,
            "driving_style_code": driving_style_code,
            "driving_style": driving_style,
            "baseline_efficiency_wh_per_km": baseline_efficiency_wh_per_km,
            "actual_wh_per_km": actual_wh_per_km,
        }
    )


def main():
    os.makedirs(DATA_DIR, exist_ok=True)

    wait_df = generate_wait_time_data()
    wait_path = os.path.join(DATA_DIR, "synthetic_wait_time.csv")
    wait_df.to_csv(wait_path, index=False)
    print(f"Wrote {len(wait_df)} rows to {wait_path}")

    range_df = generate_range_data()
    range_path = os.path.join(DATA_DIR, "synthetic_range.csv")
    range_df.to_csv(range_path, index=False)
    print(f"Wrote {len(range_df)} rows to {range_path}")


if __name__ == "__main__":
    main()
