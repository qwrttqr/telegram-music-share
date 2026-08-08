from pydantic import BaseModel


class ResponseUserCreation(BaseModel):
    success: bool
    message: str
