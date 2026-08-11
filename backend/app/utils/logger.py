import logging.config
from logging import Logger
import pathlib
import os

LOG_PATH = os.path.join(pathlib.Path(__file__).parent.parent.parent.resolve(), "logs", "app_logs.txt")

os.makedirs(os.path.dirname(LOG_PATH), exist_ok=True)

LOGGER_CONFIG = {
    "version": 1,
    "disable_existing_loggers": False,
    "formatters": {
        "standard": {
            "format": "%(asctime)s [%(levelname)s] %(name)s: %(message)s",
            "datefmt": "%Y-%m-%d %H:%M:%S"
        }
    },
    "handlers": {
        "file_handler": {
            "class": "logging.handlers.RotatingFileHandler",
            "filename": LOG_PATH,
            "maxBytes": 5242880,
            "backupCount": 3,
            "mode": "a",
            "formatter": "standard",
            "level": "ERROR",
            "encoding": "utf-8"
        }
    },
    "loggers": {
        # Your custom application logger
        "app_logger": {
            "handlers": ["file_handler"],
            "level": "DEBUG",
            "propagate": False,
        },
    }
}

logging.config.dictConfig(LOGGER_CONFIG)

def get_logger() -> Logger:
    logger = logging.getLogger("app_logger")
    return logger