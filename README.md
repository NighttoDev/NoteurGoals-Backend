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
cd NoteurGoals-Backend

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

### Sử dụng Makefile (tùy chọn, nhanh gọn)
```bash
# Khởi chạy dev, xem logs, mở bash
make dev-up
make dev-logs
make app-bash

# Artisan nhanh
a> make artisan CMD="migrate --seed"
a> make artisan CMD="route:list"

# Worker & Vite
make worker-up
make worker-logs
make vite-restart

# Sửa quyền và clear cache
make perms
make cache-clear
```


### Khởi tạo lần đầu (sau khi clone)
```bash
# Build images và khởi chạy containers (development)
docker-compose -f docker-compose.dev.yml build --no-cache
docker-compose -f docker-compose.dev.yml up -d

# Cài dependencies PHP và chuẩn bị ứng dụng
docker exec -it app_dev bash -lc "composer install"
docker exec -it app_dev bash -lc "cp -n .env.example .env || true"
docker exec -it app_dev bash -lc "php artisan key:generate"
docker exec -it app_dev bash -lc "php artisan migrate"
docker exec -it app_dev bash -lc "php artisan storage:link"
```

### Docker
```bash
# Development
docker-compose -f docker-compose.dev.yml up -d                 # Khởi chạy
docker-compose -f docker-compose.dev.yml down                  # Dừng
docker-compose -f docker-compose.dev.yml logs -f               # Xem toàn bộ logs
docker-compose -f docker-compose.dev.yml logs -f app           # Logs PHP-FPM
docker-compose -f docker-compose.dev.yml logs -f nginx         # Logs Nginx
docker-compose -f docker-compose.dev.yml logs -f vite          # Logs Vite

# Restart một service
docker-compose -f docker-compose.dev.yml restart app

# Xoá containers, networks và volumes ẩn (cẩn thận)
docker-compose -f docker-compose.dev.yml down -v --remove-orphans

# Production
docker-compose up -d      # Khởi chạy
docker-compose down       # Dừng
```

### Laravel (trong container)
```bash
# Vào container app (development)
docker exec -it app_dev bash

# Cache/route/config
composer dump-autoload
php artisan route:clear && php artisan config:clear && php artisan cache:clear
php artisan route:cache && php artisan config:cache
php artisan optimize:clear && php artisan optimize

# Database
php artisan migrate                     # Chạy migration
php artisan migrate --seed              # Migration + seed
php artisan migrate:fresh --seed        # Reset DB + seed (cẩn thận)
php artisan db:monitor                  # Nếu có package hỗ trợ

# Tinker & logs
php artisan tinker
tail -f storage/logs/laravel.log

# Liên kết storage
php artisan storage:link

# Kiểm tra routes
php artisan route:list

# Chạy test
php artisan test -v
```

### Queue worker (chat, jobs nền)
```bash
# Khởi chạy/dừng worker service (development)
docker-compose -f docker-compose.dev.yml up -d worker
docker-compose -f docker-compose.dev.yml restart worker
docker-compose -f docker-compose.dev.yml logs -f worker

# Quản lý job lỗi
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush

# Chạy worker tạm thời trong container app
php artisan queue:work --sleep=3 --tries=3
```

### Quyền file & fix lỗi ghi log
```bash
# Trong container app_dev
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwx storage bootstrap/cache
```

### Vite/Frontend (development)
```bash
# Vào container vite
docker exec -it noteurgoals_vite sh

# Cài package mới (ví dụ)
npm i axios
npm i -D tailwindcss

# Nếu gặp lỗi HMR, restart vite
docker-compose -f docker-compose.dev.yml restart vite
```


## 📘 Tham khảo thêm

- Xem thêm Terminal Cheatsheet chi tiết: TERMINAL_CHEATSHEET.md
- Lưu ý: Một số máy dùng lệnh `docker compose` thay cho `docker-compose`.
- Windows/PowerShell: Có thể dùng `bash -lc "..."` để chạy nhiều lệnh trong một phiên.

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