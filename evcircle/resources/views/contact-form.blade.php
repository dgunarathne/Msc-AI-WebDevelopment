<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Submission | EVCircle</title>
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
        
        /* Email Container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        }
        
        /* Header */
        .email-header {
            background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        
        .logo {
            max-width: 180px;
            height: auto;
            margin-bottom: 20px;
        }
        
        .email-title {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.25px;
        }
        
        /* Content */
        .email-content {
            padding: 40px;
        }
        
        /* Card */
        .card {
            background: #f0fff4;
            border-left: 4px solid #38a169;
            border-radius: 0 12px 12px 0;
            padding: 24px;
            margin-bottom: 32px;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 12px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #2f855a;
        }
        
        /* Message Box */
        .message-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
            border-left: 3px solid #cbd5e0;
        }
        
        /* Action Button */
        .action-button {
            display: inline-block;
            background: linear-gradient(to right, #38a169, #2f855a);
            color: white;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 12px rgba(56, 161, 105, 0.2);
            transition: all 0.2s ease;
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(56, 161, 105, 0.3);
        }
        
        /* Footer */
        .email-footer {
            text-align: center;
            padding: 32px;
            color: #718096;
            font-size: 14px;
            border-top: 1px solid #e2e8f0;
            background: #f7faf7;
        }
        
        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                border-radius: 0;
            }
            
            .email-content {
                padding: 30px 20px;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="{{ asset('images/logo-full.png') }}" alt="EVCircle" class="logo">
            <h1 class="email-title">New Contact Submission</h1>
        </div>
        
        <div class="email-content">
            <div class="card">
                <div class="detail-grid">
                    <div class="detail-label">Name:</div>
                    <div>{{ $data['name'] }}</div>
                    
                    <div class="detail-label">Email:</div>
                    <div>
                        <a href="mailto:{{ $data['email'] }}" style="color: #3182ce; text-decoration: none;">
                            {{ $data['email'] }}
                        </a>
                    </div>
                    
                    <div class="detail-label">Subject:</div>
                    <div>{{ $data['subject'] }}</div>
                    
                    <div class="detail-label">IP Address:</div>
                    <div>{{ $data['ip'] }}</div>
                    
                    <div class="detail-label">Date:</div>
                    <div>{{ now()->format('M j, Y \a\t g:i A') }}</div>
                </div>
            </div>
            
            <h2 style="font-size: 18px; color: #2f855a; margin-bottom: 12px;">Message Content:</h2>
            <div class="message-box">
                {{ $data['message'] }}
            </div>
            
            <div style="text-align: center; margin-top: 32px;">
                <a href="mailto:{{ $data['email'] }}?subject=Re: {{ $data['subject'] }}" class="action-button">
                    Reply to {{ $data['name'] }}
                </a>
            </div>
        </div>
        
        <div class="email-footer">
            <p style="margin: 0 0 8px 0;"><strong>EVCircle Team</strong></p>
            <p style="margin: 0 0 16px 0; color: #4a5568;">Driving Sri Lanka's electric revolution</p>
            <p style="margin: 0; font-size: 13px; color: #a0aec0;">© {{ date('Y') }} EVCircle. All rights reserved.</p>
        </div>
    </div>
</body>
</html>