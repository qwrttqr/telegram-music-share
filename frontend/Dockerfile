FROM node:22-alpine AS base
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install

FROM base AS dev
COPY . .
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

FROM base AS build
COPY . .
RUN npm run build