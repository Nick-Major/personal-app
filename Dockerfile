FROM node:18-alpine
WORKDIR /app
ENV PATH /app/node_modules/.bin:$PATH
COPY package.json .
RUN yarn install
COPY . .
EXPOSE 3000
CMD ["vite", "--host", "0.0.0.0"]
