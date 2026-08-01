FROM node:22-alpine AS BASE
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install

FROM BASE AS DEVELOPMENT
COPY . .
CMD ["npm", "run", "dev"]

FROM BASE AS BUILD
COPY . .
RUN npm run build