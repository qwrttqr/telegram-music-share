from fastapi import FastAPI
from routing import common_router


app = FastAPI()

app.include_router(common_router)