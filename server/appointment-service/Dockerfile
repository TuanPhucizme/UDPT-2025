# Bước 1: Chọn base image là Node.js phiên bản 16 (hoặc phiên bản bạn dùng)
FROM node:16-alpine

# Bước 2: Tạo và đặt thư mục làm việc bên trong container
WORKDIR /src

# Bước 3: Sao chép package.json và package-lock.json
# Tách riêng bước này để tận dụng cache của Docker. Lệnh RUN tiếp theo chỉ chạy lại
# khi 2 file này thay đổi, giúp build nhanh hơn.
COPY package*.json ./

# Bước 4: Cài đặt các dependencies trong môi trường production
RUN npm install --only=production

# Bước 5: Sao chép toàn bộ mã nguồn còn lại của service
COPY src/ .

# Bước 6: Expose cổng mà ứng dụng sẽ lắng nghe bên trong container
# Dựa vào code của bạn, đó là biến PORT, chúng ta đã đặt là 3000 trong file .env
EXPOSE 3002

# Bước 7: Lệnh để khởi chạy ứng dụng
# Sử dụng "node index.js" vì bạn dùng cú pháp ES Module (import/export)
CMD [ "node", "index.js" ]