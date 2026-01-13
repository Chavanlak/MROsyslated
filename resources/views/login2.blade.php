<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <title>Login - MRO System</title>

    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .mdi { font-size: 48px; color: #0579f5; text-align: center; margin-bottom: 10px; }
        .btn-login { background-color: #0579f5; color: #fff; width: 100%; border-radius: 8px; padding: 10px; transition: 0.3s; }
        .btn-login:hover { background-color: #025fd1; transform: translateY(-1px); }
        .form-control, .form-select { border-radius: 8px; }
    </style>
</head>
<body>
  
    <div class="flex justify-center mt-12">
        <img src="{{ asset('images/MROlogo.png') }}" alt="MRO Logo" class="w-48 h-auto md:w-64 lg:w-80">
    </div>

    <div class="login-container">
        <span class="mdi mdi-account-circle"></span>
        <h2 class="text-center mb-4 text-2xl font-bold text-gray-800">เข้าสู่ระบบ</h2>

        <form action="/loginpost" method="POST">
            @csrf <div class="mb-3">
                <label class="form-label text-sm font-medium text-gray-700">รหัสพนักงาน</label>
                <input type="text" class="form-control" placeholder="ระบุรหัสพนักงาน" name="staffcode" required autofocus>
            </div>
        
            <div class="mb-3">
                <label class="form-label text-sm font-medium text-gray-700">รหัสผ่าน</label>
                <input type="password" class="form-control" placeholder="ระบุรหัสผ่าน" name="staffpassword" required>
            </div>
        
            <div class="mb-4">
                <label for="login_mode" class="form-label text-sm font-medium text-gray-700">เลือกประเภทการเข้าใช้งาน</label>
                <select class="form-select" name="login_mode" id="login_mode">
                    <option value="repair" selected>แจ้งซ่อม (Front Staff)</option>
                    <option value="storefront">หน้าร้าน/Dashboard (Front Staff)</option>
                    <option value="admintech">Dashboard (Admin ช่าง)</option>
                    <option value="office">Dashboard (ธุรการ)</option>
                </select>
            </div>
        
            <button type="submit" class="btn btn-login font-bold shadow-sm">เข้าสู่ระบบ</button>
            
            @if (session('error'))
                <div class="alert alert-danger mt-4 py-2 small flex items-center">
                    <i class="mdi mdi-alert-circle me-2"></i> {{ session('error') }}
                </div>
            @endif
        </form>
    </div>

</body>
</html>