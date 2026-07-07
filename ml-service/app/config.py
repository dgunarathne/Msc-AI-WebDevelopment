from pydantic_settings import BaseSettings


class Settings(BaseSettings):
    osrm_base_url: str = "https://router.project-osrm.org"
    model_dir: str = "models"
    host: str = "0.0.0.0"
    port: int = 8000

    class Config:
        env_file = ".env"
        protected_namespaces = ("settings_",)


settings = Settings()
