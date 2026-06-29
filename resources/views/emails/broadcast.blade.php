<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailSubject }}</title>
    <style>
        body {
            background-color: #FAF9F6;
            color: #111111;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            margin: 0;
            padding: 40px 20px;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background-color: #FFFFFF;
            border: 1px solid #E4E4E7;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #F4F4F5;
            padding: 24px;
            border-bottom: 1px solid #E4E4E7;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: #111111;
        }
        .content {
            padding: 32px 24px;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            background-color: #6366F1;
            color: #FFFFFF;
            font-size: 8px;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            border-radius: 4px;
            margin-bottom: 16px;
        }
        .title {
            font-size: 20px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            color: #111111;
            line-height: 1.3;
        }
        .message-body {
            font-size: 13px;
            line-height: 1.6;
            color: #404040;
            white-space: pre-line;
        }
        .footer {
            background-color: #FAF9F6;
            padding: 24px;
            border-top: 1px solid #E4E4E7;
            text-align: center;
            font-size: 10px;
            color: #71717A;
            line-height: 1.5;
        }
        .footer p {
            margin: 0;
        }
        .footer .highlight {
            font-weight: bold;
            color: #111111;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Brand Header -->
        <div class="header">
            <h1>JARTS ISP MANAGEMENT</h1>
        </div>

        <!-- Email Body -->
        <div class="content">
            <div class="badge">Sistem Siaran</div>
            <h2 class="title">{{ $emailSubject }}</h2>
            <div class="message-body">{!! nl2br(e($emailMessage)) !!}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Email ini dikirim oleh <span class="highlight">Master Administrator</span> sistem.</p>
            <p style="margin-top: 4px;">Pemberitahuan sistem global untuk seluruh admin mitra penyewa portal.</p>
        </div>
    </div>
</body>
</html>
