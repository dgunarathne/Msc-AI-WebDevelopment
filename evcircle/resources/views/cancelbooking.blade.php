<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancelled | EVCircle</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            line-height: 1.6;
            color: #2d3748;
            background-color: #f7faf7;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Container */
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .header h1 {
            margin: 10px 0 5px;
            font-size: 24px;
            font-weight: 700;
        }
        
        /* Content */
        .content {
            padding: 30px;
        }
        
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #1a202c;
        }
        
        /* Status Card */
        .status-card {
            background: #fff5f5;
            border-left: 4px solid #e53e3e;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        
        .status-card h2 {
            color: #e53e3e;
            margin-top: 0;
            font-size: 20px;
        }
        
        /* Details */
        .details {
            margin: 25px 0;
            padding: 0;
        }
        
        .detail-item {
            display: flex;
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #4a5568;
            min-width: 120px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 25px;
            color: #718096;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
            background: #f7faf7;
        }
        
        .footer p {
            margin: 8px 0;
        }
        
        .footer a {
            color: #38a169;
            text-decoration: none;
            font-weight: 500;
        }
        
        .copyright {
            font-size: 12px;
            margin-top: 15px;
            opacity: 0.7;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                border-radius: 0;
                margin: 0;
            }
            
            .content, .header {
                padding: 25px 15px;
            }
            
            .detail-item {
                flex-direction: column;
            }
            
            .detail-label {
                margin-bottom: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <!-- Replace with your actual logo path -->
            <img src="{{ asset('logo.png') }}" alt="EVCircle Logo" class="logo">
            <h1>Booking Cancellation</h1>
        </div>
        
        <div class="content">
            <p class="greeting">Dear EVC User,</p>
            
            <div class="status-card">
                <h2>Your booking has been cancelled</h2>
                <p>We've processed your booking cancellation request.</p>
            </div>
            
            <p>If this cancellation was unexpected or you need any assistance, please contact our support team.</p>
            
            <p>We hope to serve you again in the future. Thank you for supporting sustainable mobility with EVCircle.</p>
        </div>
        
        <div class="footer">
            <p><strong>EVCircle Team</strong><br>
            Driving Sri Lanka's electric revolution</p>
            <p><a href="https://www.evcircle.lk">www.evcircle.lk</a> | 
            <a href="mailto:info@evcircle.lk">info@evcircle.lk</a></p>
            <p class="copyright">© 2023 EVCircle. All rights reserved.</p>
        </div>
    </div>
</body>
</html>