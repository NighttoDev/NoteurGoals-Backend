# NoteurGoals Backend

## Hướng Dẫn Cài Đặt Môi Trường Phát Triển

1. Clone repository:
```bash
git clone <repository-url>
cd NoteurGoals-Backend
```

2. Khởi động Docker containers:
```bash
docker-compose up -d
```

3. Cài đặt dependencies và thiết lập Laravel:
```bash
docker-compose exec app bash
composer install
```

## Quy Trình Phát Triển

1. Luôn cập nhật code mới nhất trước khi bắt đầu làm việc:
```bash
git pull origin main
```

2. Tạo nhánh mới cho tính năng của bạn:
```bash
git checkout -b feature/ten-tinh-nang
```

3. Sau khi hoàn thành công việc:
```bash
git add .
git commit -m "Mô tả thay đổi"
git push origin feature/ten-tinh-nang
```

4. Tạo Pull Request trên GitHub

## Lưu Ý Quan Trọng

- File `docker-compose.yml` đã được cấu hình sẵn và sẽ tự động tạo file `.env` khi container khởi động
- Không cần tạo hoặc chỉnh sửa file `.env` thủ công
- Các biến môi trường đã được cấu hình trong `docker-compose.yml`
- Database đã được cấu hình sẵn và kết nối đến remote server
- File upload sẽ được lưu trữ trên cloud storage (AWS S3/Google Cloud Storage) hoặc hosting tùy theo môi trường

## Các Vấn Đề Thường Gặp

- Nếu gặp lỗi về quyền truy cập, chạy lệnh:
  ```bash
  docker-compose exec app chown -R www-data:www-data /var/www
  ```
- Nếu cần build lại containers:
  ```bash
  docker-compose down
  docker-compose up -d --build
  ```

## Thông Tin Liên Hệ
- Project Manager: [Thông tin liên hệ]
- Technical Lead: [Thông tin liên hệ]
- DevOps: [Thông tin liên hệ]