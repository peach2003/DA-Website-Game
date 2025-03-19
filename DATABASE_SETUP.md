# Hướng dẫn khắc phục lỗi kết nối MariaDB

## Lỗi hiện tại

```
Fatal error: Uncaught mysqli_sql_exception: Host 'localhost' is not allowed to connect to this MariaDB server
```

## Các bước khắc phục

1. Dừng dịch vụ MySQL/MariaDB trong XAMPP Control Panel

2. Tạo database và cấp quyền cho user:

   - Mở Command Prompt với quyền Administrator
   - Di chuyển đến thư mục MySQL của XAMPP:
     ```
     cd C:\xampp\mysql\bin
     ```
   - Đăng nhập vào MySQL với tài khoản root:
     ```
     mysql -u root
     ```
   - Thực hiện các lệnh SQL sau:
     ```sql
     CREATE DATABASE IF NOT EXISTS game_portal;
     CREATE USER 'root'@'localhost' IDENTIFIED BY '';
     GRANT ALL PRIVILEGES ON game_portal.* TO 'root'@'localhost';
     FLUSH PRIVILEGES;
     ```

3. Import cấu trúc database:

   - Trong Command Prompt, chạy lệnh:
     ```
     mysql -u root game_portal < "C:\xampp\htdocs\Project\web_game\database.sql"
     ```

4. Kiểm tra file cấu hình MySQL:

   - Mở file `C:\xampp\mysql\bin\my.ini`
   - Tìm và đảm bảo có dòng:
     ```ini
     bind-address = 127.0.0.1
     ```

5. Khởi động lại MySQL trong XAMPP Control Panel

## Kiểm tra kết nối

Sau khi thực hiện các bước trên, hãy thử truy cập lại trang web của bạn. Nếu vẫn gặp lỗi, có thể thử các bước sau:

1. Kiểm tra trạng thái MySQL:

   ```
   mysql -u root -e "STATUS"
   ```

2. Kiểm tra danh sách users:

   ```sql
   SELECT user, host FROM mysql.user;
   ```

3. Kiểm tra quyền của user:
   ```sql
   SHOW GRANTS FOR 'root'@'localhost';
   ```
