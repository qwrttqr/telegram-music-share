from fastapi import FastAPI
from routing import user_router, posts_router,friends_router


app = FastAPI()

app.include_router(user_router)
app.include_router(posts_router)
app.include_router(friends_router)