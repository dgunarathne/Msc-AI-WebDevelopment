from pydantic import BaseModel, Field


class WaitTimeRequest(BaseModel):
    charger_id: int
    connector_type: str = "Type2"
    number_of_connectors: int = Field(1, ge=1, le=20)
    current_queue_length: int = Field(0, ge=0)
    active_sessions: int = Field(0, ge=0)
    avg_session_minutes: float = Field(45.0, ge=1)
    hour_of_day: int = Field(..., ge=0, le=23)
    day_of_week: int = Field(..., ge=0, le=6)
    is_dc_fast: bool = False


class WaitTimeResponse(BaseModel):
    charger_id: int
    predicted_wait_minutes: float
    confidence: str
