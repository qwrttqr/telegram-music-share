FROM python:3.12-slim AS base
WORKDIR /backend
COPY ./requirements.txt /backend/requirements.txt
RUN pip install --no-cache-dir --upgrade -r /backend/requirements.txt

FROM base AS dev
COPY ./app /backend/app
CMD ["fastapi", "dev", "app/main.py", "--port", "8000", "--host", "0.0.0.0"]

FROM base AS prod
COPY ./app /backend/app
CMD ["fastapi", "run", "app/main.py", "--port", "8000", "--host", "0.0.0.0"]