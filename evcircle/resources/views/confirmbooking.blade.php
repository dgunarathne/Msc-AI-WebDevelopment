<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Confirmation | EVCircle</title>
  <style>
    /* Base Styles */
    body {
      font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
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
      background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
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
    
    .header p {
      margin: 0;
      opacity: 0.9;
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
    
    /* Message Box */
    .message {
      background: #f0fff4;
      border-left: 4px solid #38a169;
      border-radius: 0 8px 8px 0;
      padding: 20px;
      margin: 25px 0;
    }
    
    /* Eco Tip */
    .eco-tip {
      font-size: 14px;
      color: #4a5568;
      font-style: italic;
      margin-top: 30px;
      padding: 15px;
      background: #f8f9fa;
      border-radius: 8px;
      border-left: 3px solid #a0aec0;
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
      
      .logo {
        max-width: 140px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <img src="{{ asset('logo.png') }}" alt="EVCircle Logo" class="logo">
      <h1>Booking Confirmed</h1>
      <p>EVCircle - Sustainable Mobility</p>
    </div>
    
    <div class="content">
      <p class="greeting">Dear Valued Customer,</p>
      
      <div class="message">
        <p>Thank you for choosing EVCircle! Your booking has been successfully confirmed.</p>
        <p>By opting for electric vehicle charging, you're helping reduce carbon emissions and create a cleaner environment.</p>
      </div>
      
      <p>We appreciate your commitment to sustainable transportation. Your support helps us expand our green charging network across the country.</p>
      
      <div class="eco-tip">
        Eco Tip: Charging during off-peak hours (9PM-6AM) helps balance grid load and often uses cleaner energy.
      </div>
    </div>
    
    <div class="footer">
      <p><strong>EVCircle Team</strong><br>
      Driving the green revolution forward</p>
      <p><a href="https://www.evcircle.lk">www.evcircle.lk</a> | <a href="mailto:info@evcircle.lk">info@evcircle.lk</a></p>
      <p class="copyright">Please consider the environment before printing this email</p>
    </div>
  </div>
</body>
</html>