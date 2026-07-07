import json
import os

import joblib
import pandas as pd
from sklearn.ensemble import GradientBoostingRegressor
from sklearn.metrics import mean_absolute_error, r2_score
from sklearn.model_selection import train_test_split

BASE_DIR = os.path.join(os.path.dirname(__file__), "..")
DATA_PATH = os.path.join(BASE_DIR, "data", "synthetic_wait_time.csv")
MODEL_PATH = os.path.join(BASE_DIR, "models", "wait_time_model.pkl")
METADATA_PATH = os.path.join(BASE_DIR, "models", "feature_metadata.json")

FEATURES = [
    "number_of_connectors",
    "current_queue_length",
    "active_sessions",
    "avg_session_minutes",
    "hour_of_day",
    "day_of_week",
    "is_weekend",
    "is_dc_fast",
]
TARGET = "wait_minutes"


def main():
    df = pd.read_csv(DATA_PATH)
    X = df[FEATURES]
    y = df[TARGET]

    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

    model = GradientBoostingRegressor(n_estimators=200, max_depth=3, learning_rate=0.1, random_state=42)
    model.fit(X_train, y_train)

    preds = model.predict(X_test)
    mae = mean_absolute_error(y_test, preds)
    r2 = r2_score(y_test, preds)
    print(f"[wait-time model] holdout MAE={mae:.2f} minutes, R2={r2:.3f}")

    os.makedirs(os.path.dirname(MODEL_PATH), exist_ok=True)
    joblib.dump(model, MODEL_PATH)
    print(f"Saved model to {MODEL_PATH}")

    metadata = {}
    if os.path.exists(METADATA_PATH):
        with open(METADATA_PATH) as f:
            metadata = json.load(f)
    metadata["wait_time_features"] = FEATURES
    with open(METADATA_PATH, "w") as f:
        json.dump(metadata, f, indent=2)


if __name__ == "__main__":
    main()
