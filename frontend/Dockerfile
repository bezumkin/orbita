FROM node:22-alpine
WORKDIR /usr/app

COPY . .

RUN npm install --omit=dev

RUN npm run build

CMD ["node", "./.output/server/index.mjs"]
