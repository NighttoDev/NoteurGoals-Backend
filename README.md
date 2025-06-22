# NoteurGoals Backend

Hệ thống quản lý mục tiêu với Laravel backend và giao diện admin React.

## 🚀 Khởi Chạy Nhanh

### Yêu Cầu
- Docker & Docker Compose
- Git

### Chạy Development (Khuyến nghị)
```bash
# Clone project
git clone <repository-url>
cd NoteurGoals-Backend-1

# Chạy development với hot-reload
docker-compose -f docker-compose.dev.yml up -d

# Truy cập: http://localhost:8000
```

### Chạy Production
```bash
# Chạy production environment
docker-compose up -d
```

## 🎯 Truy Cập Hệ Thống

### Admin Panel
- **Đăng nhập**: `http://localhost:8000/login`
- **Dashboard**: `http://localhost:8000/dashboard`
- **Chức năng**: Quản lý Users, Goals, Notes, Events, Subscriptions

### API Endpoints
- **Base URL**: `http://localhost:8000/api`
- **Health Check**: `http://localhost:8000/api/health`

## 🏗️ Công Nghệ

- **Backend**: Laravel 11 + PHP 8.2
- **Frontend**: React + Inertia.js
- **Database**: MySQL (remote)
- **Container**: Docker + Nginx
- **Build**: Vite (hot-reload trong development)


## 🛠️ Lệnh Thường Dùng

### Docker
```bash
# Development
docker-compose -f docker-compose.dev.yml up -d     # Khởi chạy
docker-compose -f docker-compose.dev.yml down      # Dừng
docker-compose -f docker-compose.dev.yml logs -f   # Xem logs

# Production
docker-compose up -d      # Khởi chạy
docker-compose down       # Dừng
```

### Laravel (trong container)
```bash
# Vào container
docker exec -it app_dev bash

# Clear cache
php artisan route:clear
php artisan config:clear
php artisan cache:clear

# Migration
php artisan migrate

# Xem routes
php artisan route:list
```

## 🐛 Sửa Lỗi Thường Gặp

### 1. Màn hình trắng
```bash
# Kiểm tra container Vite
docker logs noteurgoals_vite

# Restart nếu cần
docker-compose -f docker-compose.dev.yml restart vite
```

### 2. Route không tìm thấy
```bash
docker exec -it app_dev bash
php artisan route:clear
php artisan ziggy:generate
```

### 3. Lỗi database
- Kiểm tra thông tin database trong `docker-compose.dev.yml`
- Đảm bảo kết nối internet để truy cập remote database

### 4. Container không chạy
```bash
# Xem logs để debug
docker-compose -f docker-compose.dev.yml logs app_dev
docker-compose -f docker-compose.dev.yml logs nginx
docker-compose -f docker-compose.dev.yml logs vite
```

## 📁 Cấu Trúc Project

```
├── src/                        # Laravel application
│   ├── app/Http/Controllers/   # Controllers
│   ├── resources/js/           # React components
│   ├── routes/                 # Routes định nghĩa
│   └── database/              # Migration & models
├── docker-compose.yml         # Production setup
├── docker-compose.dev.yml     # Development setup
├── php/Dockerfile            # Production PHP image
└── php/Dockerfile.dev        # Development PHP image
```

## 🚀 Development Workflow

1. **Start containers**: `docker-compose -f docker-compose.dev.yml up -d`
2. **Tạo admin user** (lần đầu tiên)
3. **Truy cập**: `http://localhost:8000/login`
4. **Edit code**: Thay đổi trong `src/` sẽ tự động reload
5. **Debug**: Xem logs container nếu có lỗi

## ⚡ Hot Reload

- **PHP**: Tự động reload khi sửa file `.php`
- **React**: Vite dev server tự động reload khi sửa file `.jsx`
- **Styles**: CSS/Tailwind tự động compile

## 📞 Hỗ Trợ

- **Lỗi kỹ thuật**: Xem phần Sửa Lỗi Thường Gặp
- **Feature mới**: Tạo GitHub issue
- **Database**: Remote MySQL đã được cấu hình sẵn

---

**Lưu ý**: Đây là admin backend. Frontend user ở repository riêng.