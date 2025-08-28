# Bước 1: Chọn base image
FROM node:16-alpine

# Bước 2: Đặt thư mục làm việc là /app
WORKDIR /app

# Bước 3: Sao chép các file package.json và package-lock.json
COPY package*.json ./

# Bước 4: Cài đặt dependencies
RUN npm install

# Bước 5: Sao chép TOÀN BỘ mã nguồn còn lại vào thư mục làm việc
COPY . .

# Bước 6: Expose cổng
EXPOSE 3005

# Lệnh để khởi chạy ứng dụng
CMD [ "node", "server.js" ]