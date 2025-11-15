<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking System - Welcome</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 50px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .logo {
            font-size: 60px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            font-size: 36px;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            font-size: 18px;
            margin-bottom: 40px;
        }
        
        .status-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #28a745;
        }
        
        .status-card h3 {
            color: #28a745;
            margin-bottom: 10px;
        }
        
        .status-card p {
            color: #555;
            line-height: 1.6;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 30px 0;
        }
        
        .info-box {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
        }
        
        .info-box h4 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .info-box p {
            color: #333;
            font-size: 16px;
            font-weight: bold;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .footer {
            margin-top: 30px;
            color: #999;
            font-size: 14px;
        }
        
        .check-icon {
            color: #28a745;
            font-size: 24px;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🏨</div>
        <h1>Hotel Booking System</h1>
        <p class="subtitle">Welcome to Your Mini Project</p>
        
        <div class="status-card">
            <h3><span class="check-icon">✓</span>System Status: Online</h3>
            <p>Your XAMPP server is running successfully! You can now start developing your hotel booking system.</p>
        </div>
        
        <div class="info-grid">
            <div class="info-box">
                <h4>📅 Project Date</h4>
                <p><?php echo date('d M Y'); ?></p>
            </div>
            <div class="info-box">
                <h4>⏰ Server Time</h4>
                <p><?php echo date('H:i:s'); ?></p>
            </div>
            <div class="info-box">
                <h4>💻 Server</h4>
                <p>XAMPP + PHP</p>
            </div>
            <div class="info-box">
                <h4>🗄️ Database</h4>
                <p>MySQL</p>
            </div>
        </div>
        
        <div style="margin: 30px 0;">
            <a href="test_connection.php" class="btn">Test Database Connection</a>
            <a href="pages/rooms.php" class="btn btn-secondary">View Rooms</a>
        </div>
        
        <div class="footer">
            <p>📌 Project Location: C:\xampp\htdocs\Hotel Booking System</p>
            <p>🔗 GitHub: Connected & Ready</p>
        </div>
    </div>
</body>
</html>