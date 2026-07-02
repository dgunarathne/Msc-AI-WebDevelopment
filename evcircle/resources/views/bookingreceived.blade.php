<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Booking Notification | EVCircle</title>
  <style>
    /* Base Styles */
    body {
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
      line-height: 1.6;
      color: #2d3748;
      background-color: #f7faf7;
      margin: 0;
      padding: 0;
    }
    
    /* Email Container */
    .email-container {
      max-width: 600px;
      margin: 20px auto;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    /* Header */
    .email-header {
      background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
      color: white;
      padding: 30px;
      text-align: center;
    }
    
    .logo {
      max-width: 180px;
      height: auto;
    }
    
    /* Content */
    .email-content {
      padding: 30px;
    }
    
    .notification-message {
      font-size: 18px;
      margin-bottom: 25px;
      color: #1a202c;
    }
    
    .booking-details {
      background: #f0fff4;
      border-left: 4px solid #38a169;
      border-radius: 0 8px 8px 0;
      padding: 20px;
      margin: 25px 0;
    }
    
    .detail-item {
      margin-bottom: 12px;
    }
    
    .detail-label {
      font-weight: 600;
      color: #2f855a;
    }
    
    /* Footer */
    .email-footer {
      text-align: center;
      padding: 25px;
      color: #718096;
      font-size: 14px;
      border-top: 1px solid #e2e8f0;
      background: #f7faf7;
    }
    
    .support-link {
      color: #38a169;
      text-decoration: none;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <div class="email-header">
      <img src="https://evcircle.lk/images/logo-white.png" alt="EVCircle" class="logo">
      <h1 style="margin:15px 0 5px 0;font-size:24px;">New Booking Notification</h1>
    </div>
    
    <div class="email-content">
      <p class="notification-message">Dear Partner,</p>
      
      <p>We're pleased to inform you that your EV charging station has received a new booking through the EVCircle network.</p>
      
      <div class="booking-details">
        <div class="detail-item">
          <span class="detail-label">Booking Status:</span> Confirmed
        </div>
        <div class="detail-item">
          <span class="detail-label">Next Steps:</span> Please ensure your station is ready for the scheduled charging session
        </div>
        <div class="detail-item">
          <span class="detail-label">Reminder:</span> You'll receive the complete booking details 24 hours before the scheduled time
        </div>
      </div>
      
      <p>To manage your bookings and station availability, please log in to your Partner Dashboard.</p>
      
      <p>If you have any questions about this booking or need assistance, our partner support team is available to help.</p>
    </div>
    
    <div class="email-footer">
      <p><strong>EVCircle Partner Support</strong></p>
      <p>Email: <a href="mailto:info@evcircle.lk" class="support-link">info@evcircle.lk</a></p>
      <p style="margin-top:15px;font-size:13px;">© 2023 EVCircle. All rights reserved.</p>
    </div>
  </div>
</body>
</html>