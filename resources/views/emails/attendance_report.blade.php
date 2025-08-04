<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Daily Attendance Report - {{ $date }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            margin: 10px 0;
        }
        ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0c0238;
            color: #fff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #0e447d;
        }
        .footer {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Daily Attendance Report - {{ $date }}</h1>
        <p>Dear Admins and HR,</p>
        <p>Attached is the daily attendance report for {{ $date }} in {{ strtoupper($format ?? 'Excel') }} format. The report includes:</p>
        <ul>
            <li>Employee details (ID, Name, Department, Role)</li>
            <li>Attendance details (Login Time, Logout Time, Work Hours, Mode)</li>
            <li>Assigned tasks</li>
        </ul>
        <p>Please review the attached file for full details.</p>
        <p>Best regards,<br>ZENEROM HRM</p>
        <a href="{{ url('/attendance/todays-report') }}" style="background-color: #0c0238; color: #ffffff; text-decoration: none; padding: 9px 18px; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;">View Report Online</a>
        <div class="footer">
            <p>Thanks,<br>ZENEROM HRM</p>
        </div>
    </div>
</body>
</html>
