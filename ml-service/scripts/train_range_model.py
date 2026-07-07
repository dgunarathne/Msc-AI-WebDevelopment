import json
import os

import joblib
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, r2_score
from sklearn.model_selection import train_test_split

BASE_DIR = os.path.join(os.path.dirname(__file__), "..")
DATA_PATH = os.path.join(BASE_DIR, "data", "synthetic_range.csv")
MODEL_PATH = os.path.join(BASE_DIR, "models", "range_model.pkl")
METADATA_PATH = os.path.join(BASE_DIR, "models", "feature_metadata.json")

FEATURES = [
    "avg_speed_kmh",
    "temperature_c",
    "elevation_gain_per_km",
    "driving_style_code",
    "baseline_efficiency_wh_per_km",
]
TARGET = "actual_wh_per_km"
DRIVING_STYLE_ENCODING = {"eco": 0, "normal": 1, "aggressive": 2}


def main():
    df = pd.read_csv(DATA_PATH)
    X = df[FEATURES]
    y = df[TARGET]

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    model = RandomForestRegressor(n_estimators=200, max_depth=10, random_state=42, n_jobs=-1)
    model.fit(X_train, y_train)

    preds = model.predict(X_test)
    mae = mean_absolute_error(y_test, preds)
    r2 = r2_score(y_test, preds)
    print(f"[range model] holdout MAE={mae:.2f} Wh/km, R2={r2:.3f}")

    os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
    joblib.dump(model, MODEL_PATH)
    print(f"Saved model to {MODEL_PATH}")

    metadata = {}
    if os.path.exists(METADATA_PATH):
        with open(METADATA_PATH) as f:
            metadata = json.load(f)
    metadata["range_features"] = FEATURES
    metadata["driving_style_encoding"] = DRIVING_STYLE_ENCODING
    with open(METADATA_PATH, "w") as f:
        json.dump(metadata, f, indent=2)


if __name__ == "__main__":
    main()
