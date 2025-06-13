<?php
// filepath: c:\xampp\htdocs\NoteurGoals-Backend\src\public\social-login-test.php
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login with Social Networks</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #333; }
        .box { background-color: #f9f9f9; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        button { background: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; }
        button.facebook { background: #3b5998; }
        button.google { background: #db4437; }
        pre { background: #eee; padding: 10px; overflow: auto; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <h1>Test Social Login APIs</h1>

    <div class="box">
        <h2>1. Lấy URL đăng nhập</h2>
        
        <button type="button" class="google" onclick="getAuthUrl('google')">Lấy URL đăng nhập Google</button>
        <button type="button" class="facebook" onclick="getAuthUrl('facebook')">Lấy URL đăng nhập Facebook</button>
        
        <div id="url-result" class="hidden">
            <h3>URL đăng nhập:</h3>
            <pre id="auth-url"></pre>
            <button onclick="openAuthUrl()">Mở URL trong tab mới</button>
        </div>
    </div>

    <div class="box">
        <h2>2. Test đăng nhập trực tiếp với token</h2>
        <p>Dùng để test trực tiếp khi đã có token từ Google/Facebook (thường từ mobile/frontend)</p>
        
        <div>
            <label>Provider:</label>
            <select id="provider">
                <option value="google">Google</option>
                <option value="facebook">Facebook</option>
            </select>
        </div>
        
        <div>
            <label>Access Token/ID Token:</label>
            <input type="text" id="token" style="width: 100%" placeholder="Nhập token của bạn...">
        </div>
        
        <button type="button" onclick="loginWithToken()">Đăng nhập với token</button>
        
        <div id="token-result" class="hidden">
            <h3>Kết quả:</h3>
            <pre id="token-response"></pre>
        </div>
    </div>

    <div class="box">
        <h2>3. Test đăng nhập với Google ID Token</h2>
        <p>Đặc biệt hữu ích cho mobile</p>
        
        <div>
            <label>Google ID Token:</label>
            <input type="text" id="id-token" style="width: 100%" placeholder="Nhập Google ID token...">
        </div>
        
        <button type="button" onclick="loginWithGoogleIdToken()">Đăng nhập với Google ID Token</button>
        
        <div id="id-token-result" class="hidden">
            <h3>Kết quả:</h3>
            <pre id="id-token-response"></pre>
        </div>
    </div>

    <div class="box">
        <h2>4. Test đăng nhập xã hội đơn giản</h2>
        <p>Tạo request API đăng nhập đơn giản (không cần token)</p>
        
        <div>
            <label>Họ tên:</label>
            <input type="text" id="name" value="Người dùng test">
        </div>
        
        <div>
            <label>Email:</label>
            <input type="email" id="email" value="test@example.com">
        </div>
        
        <div>
            <label>Provider:</label>
            <select id="simple-provider">
                <option value="google">Google</option>
                <option value="facebook">Facebook</option>
            </select>
        </div>
        
        <div>
            <label>Avatar URL:</label>
            <input type="text" id="avatar" value="https://ui-avatars.com/api/?name=Test+User">
        </div>
        
        <button type="button" onclick="simpleSocialLogin()">Đăng nhập đơn giản</button>
        
        <div id="simple-result" class="hidden">
            <h3>Kết quả:</h3>
            <pre id="simple-response"></pre>
        </div>
    </div>

    <script>
        // URL lưu trữ
        let authUrl = '';
        
        // Lấy URL đăng nhập từ Google/Facebook
        // Sửa lại hàm getAuthUrl để bắt lỗi tốt hơn
        function getAuthUrl(provider) {
            document.getElementById('auth-url').textContent = 'Đang tải...';
            document.getElementById('url-result').classList.remove('hidden');
            
            fetch(`/api/auth/${provider}/url`)
                .then(response => {
                    if (!response.ok) {
                        if (response.headers.get('content-type')?.includes('text/html')) {
                            return response.text().then(html => {
                                throw new Error(`HTML Error: ${html.substring(0, 200)}...`);
                            });
                        } else {
                            return response.json().then(err => {
                                throw new Error(err.message || 'Error fetching auth URL');
                            });
                        }
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        authUrl = data.url;
                        document.getElementById('auth-url').textContent = authUrl;
                    } else {
                        document.getElementById('auth-url').textContent = 'Error: ' + 
                            JSON.stringify(data, null, 2);
                    }
                })
                .catch(error => {
                    document.getElementById('auth-url').textContent = 'Error: ' + error.message;
                });
        }
        
        // Mở URL xác thực trong tab mới
        function openAuthUrl() {
            if (authUrl) {
                window.open(authUrl, '_blank');
            }
        }
        
        // Đăng nhập với access token
        function loginWithToken() {
            const provider = document.getElementById('provider').value;
            const token = document.getElementById('token').value.trim();
            
            if (!token) {
                alert('Vui lòng nhập token!');
                return;
            }
            
            document.getElementById('token-response').textContent = 'Đang xử lý...';
            document.getElementById('token-result').classList.remove('hidden');
            
            fetch('/api/auth/social-login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    provider: provider,
                    token: token
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('token-response').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('token-response').textContent = 'Lỗi: ' + error.message;
            });
        }
        
        // Đăng nhập với Google ID token
        function loginWithGoogleIdToken() {
            const idToken = document.getElementById('id-token').value.trim();
            
            if (!idToken) {
                alert('Vui lòng nhập Google ID token!');
                return;
            }
            
            document.getElementById('id-token-response').textContent = 'Đang xử lý...';
            document.getElementById('id-token-result').classList.remove('hidden');
            
            fetch('/api/auth/google-id-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    id_token: idToken
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('id-token-response').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('id-token-response').textContent = 'Lỗi: ' + error.message;
            });
        }
        
        // Thêm API endpoint đơn giản cho social login
        function simpleSocialLogin() {
            // Dữ liệu từ form
            const data = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                provider: document.getElementById('simple-provider').value,
                avatar_url: document.getElementById('avatar').value
            };
            
            document.getElementById('simple-response').textContent = 'Đang xử lý...';
            document.getElementById('simple-result').classList.remove('hidden');
            
            fetch('/api/auth/social-login-simple', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('simple-response').textContent = JSON.stringify(data, null, 2);
            })
            .catch(error => {
                document.getElementById('simple-response').textContent = 'Lỗi: ' + error.message;
            });
        }
    </script>
</body>
</html>