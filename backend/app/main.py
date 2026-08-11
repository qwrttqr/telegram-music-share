import logging

from fastapi import FastAPI, Request, status
from fastapi.responses import JSONResponse
from routing import user_router, posts_router,friends_router
from utils.logger import get_logger

app = FastAPI()

app.include_router(user_router)
app.include_router(posts_router)
app.include_router(friends_router)

logger = get_logger()

@app.exception_handler(Exception)
async def global_exception_handler(request: Request, exc: Exception):
    logger.error(
        f"Unhandled error on {request.method} {request.url.path}: {str(exc)}",
        exc_info=True
    )

    return JSONResponse(
        status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        content={"detail": "An internal server error occurred. Please try again later."}
    )