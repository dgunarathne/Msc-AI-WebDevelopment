<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to EVCircle</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: #1a202c;
            background-color: #f7faf7;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        
        /* Container */
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            color: white;
            padding: 40px 20px 30px;
            text-align: center;
        }
        
        .logo {
            max-width: 200px;
            height: auto;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 700;
        }
        
        .header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.92;
        }
        
        /* Content */
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 20px;
            margin-bottom: 24px;
            color: #1a202c;
            font-weight: 600;
        }
        
        /* Features List */
        .features-list {
            padding-left: 20px;
            margin: 30px 0;
        }
        
        .features-list li {
            margin-bottom: 14px;
            padding-left: 8px;
            position: relative;
            line-height: 1.5;
        }
        
        .features-list li:before {
            content: "•";
            color: #38a169;
            font-weight: bold;
            display: inline-block;
            width: 1em;
            margin-left: -1em;
            position: absolute;
        }
        
        /* CTA Button */
        .cta-container {
            text-align: center;
            margin: 32px 0;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(to right, #38a169, #2f855a);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.25);
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 28px 20px;
            color: #718096;
            font-size: 14px;
            border-top: 1px solid #edf2f7;
            background: #f7faf7;
        }
        
        .footer a {
            color: #38a169;
            text-decoration: none;
            font-weight: 500;
        }
        
        .copyright {
            font-size: 13px;
            margin-top: 20px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="EVCircle Logo" class="logo">
            <h1>Welcome to EVCircle</h1>
            <p>Your journey to sustainable mobility begins here</p>
        </div>
        
        <div class="content">
            <p class="greeting">Hello {{ $name }},</p>
            
            <p>We're thrilled to welcome you to EVCircle, Sri Lanka's premier EV charging network. As a member of our community, you're joining a movement towards cleaner, smarter transportation.</p>
            
            <p>Here's what you can enjoy with your EVCircle membership:</p>
            
            <ul class="features-list">
                <li>Access to our nationwide network of fast charging stations</li>
                <li>Real-time availability and reservation system</li>
                <li>Seamless in-app payments and transaction history</li>
                <li>Exclusive member rewards and promotions</li>
                <li>24/7 customer support for all your EV needs</li>
            </ul>
            
            <div class="cta-container">
                <a href="https://evcircle.lk" class="cta-button">Start Exploring Charging Stations</a>
            </div>
            
            <p>We're committed to making your EV experience effortless and enjoyable. If you have any questions, our support team is always ready to help.</p>
        </div>
        
        <div class="footer">
            <p><strong>EVCircle Team</strong><br>
            Pioneering Sri Lanka's electric vehicle revolution</p>
            <p><a href="https://www.evcircle.lk">www.evcircle.lk</a> | 
            <a href="mailto:info@evcircle.lk">info@evcircle.lk</a></p>
            <p class="copyright">© {{ date('Y') }} EVCircle. All rights reserved.</p>
        </div>
    </div>
</body>
</html>