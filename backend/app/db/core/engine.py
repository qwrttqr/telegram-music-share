import os
from dotenv import load_dotenv
from sqlalchemy.ext.asyncio import create_async_engine, async_sessionmaker

load_dotenv()

engine = create_async_engine(
    os.environ["DB_CONN"],
    pool_pre_ping=True,  # Restores dead connections transparently
    pool_recycle=3600  # Force-recycle connections older than 1 hour
)

SessionLocal = async_sessionmaker(bind=engine, expire_on_commit=False)


async def get_db_session():
    async with SessionLocal() as session:
        yield session