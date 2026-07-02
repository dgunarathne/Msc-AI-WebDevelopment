<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt | EVCircle</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Container */
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border: 1px solid #e0e0e0;
        }
        
        /* Header */
        .header {
            background-color: #fff;
            padding: 25px 30px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .logo {
            max-width: 160px;
            height: auto;
            margin-bottom: 15px;
        }
        
        .receipt-title {
            font-size: 20px;
            font-weight: 600;
            color: #2e7d32;
            margin: 10px 0 5px;
        }
        
        /* Content */
        .content {
            padding: 30px;
        }
        
        .thank-you {
            font-size: 16px;
            margin-bottom: 25px;
            color: #444;
        }
        
        /* Receipt Details */
        .receipt-details {
            margin: 30px 0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
        .detail-label {
            font-weight: 500;
            color: #666;
        }
        
        .detail-value {
            font-weight: 500;
            text-align: right;
        }
        
        .total-row {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e0e0e0;
            font-size: 18px;
            font-weight: 600;
            color: #2e7d32;
        }
        
        /* Footer */
        .footer {
            padding: 25px;
            background-color: #f5f5f5;
            text-align: center;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #e0e0e0;
        }
        
        .support-info {
            margin-top: 30px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 6px;
            font-size: 14px;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .container {
                border-radius: 0;
                margin: 0;
                border: none;
            }
            
            .content, .header {
                padding: 20px 15px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('logo.png') }}" alt="EVCircle Logo" class="logo">
            <div class="receipt-title">Payment Receipt</div>
            <div>Recipt For EVCircle Subscription</div>
        </div>
        
        <div class="content">
            <p class="thank-you">Dear {{ $data['name'] }},<br>
            Thank you for your payment. Here's your receipt for records.</p>
            
            <div class="receipt-details">
                <div class="detail-row">
                    <span class="detail-label">Date & Time:</span>
                    <span class="detail-value">{{ $data['payment_date'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Subscription Plan:</span>
                    <span class="detail-value">{{ $data['plan_name'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Billing Period:</span>
                    <span class="detail-value">{{ $data['start_date'] }} to {{ $data['end_date'] }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Method:</span>
                    <span class="detail-value">{{ $data['payment_method'] }}</span>
                </div>
                <div class="detail-row total-row">
                    <span class="detail-label">Amount Paid:</span>
                    <span class="detail-value">LKR {{ number_format($data['amount'], 2) }}</span>
                </div>
            </div>
            
            <div class="support-info">
                <strong>Need help?</strong><br>
                Contact our support team at <a href="mailto:info@evcircle.lk">info@evcircle.lk</a> 
                or call 077 623 1796 for any questions about this transaction.
            </div>
        </div>
        
        <div class="footer">
            © 2025 EVCircle. All rights reserved.<br>
           Recipt For EVCircle Subscription
        </div>
    </div>
</body>
</html>