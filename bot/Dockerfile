FROM python:3.12-slim AS base

WORKDIR /app

ENV PYTHONDONTWRITEBYTECODE=1
ENV PYTHONUNBUFFERED=1

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

FROM base AS dev
CMD ["watchmedo", "auto-restart", "--directory=.", "--pattern=*.py", "--recursive", "--", "python", "main.py"]

FROM base AS prod
COPY main.py .
CMD ["python", "main.py"]
