<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>New Leave Request Submitted</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f2f0; font-family: Arial, sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f0f2f5; padding: 20px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
          <!-- Header / Logo -->
          <tr>
            <td style="background-color: #0c0238; padding: 20px 30px; text-align: center;">
              <img src="https://hrm.zenerom.ae/images/zenerom_logo.png" alt="ZENEROM Logo" style="max-width: 150px; height: auto;">
            </td>
          </tr>

          <!-- Main Content -->
          <tr>
            <td style="padding: 30px;">
              <h2 style="color: #0c0238; font-size: 22px; margin: 0 0 8px;">New Leave Request Submitted</h2>
              <p style="color: #333333; font-size: 16px; padding: 0; margin: 0;">Dear HR Team,</p>
              <p style="color: #333333; font-size: 16px; line-height: 1.5; padding: 0; margin: 0;">A new leave request has been submitted with the following details:</p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin-top: 20px; box-shadow: rgba(14, 63, 126, 0.06) 0px 0px 0px 1px, rgba(42, 51, 70, 0.03) 0px 1px 1px -0.5px, rgba(42, 51, 70, 0.04) 0px 2px 2px -1px, rgba(42, 51, 70, 0.04) 0px 3px 3px -1.5px, rgba(42, 51, 70, 0.03) 0px 5px 5px -2.5px, rgba(42, 51, 70, 0.03) 0px 10px 10px -5px, rgba(42, 51, 70, 0.03) 0px 24px 24px -8px;">
                <tr>
                  <td style="padding: 10px 10px;"><strong>Employee:</strong></td>
                  <td style="padding: 10px 10px;">{{ $employeeName }}</td>
                </tr>
                <tr style="background-color: #f9f9f9;">
                  <td style="padding: 10px 10px;"><strong>Leave Type:</strong></td>
                  <td style="padding: 10px 10px;">{{ $leaveType }}</td>
                </tr>
                <tr>
                  <td style="padding: 10px 10px;"><strong>Start Date:</strong></td>
                  <td style="padding: 10px 10px;">{{ $startDate }}</td>
                </tr>
                <tr style="background-color: #f9f9f9;">
                  <td style="padding: 10px 10px;"><strong>End Date:</strong></td>
                  <td style="padding: 10px 10px;">{{ $endDate }}</td>
                </tr>
                <tr>
                  <td style="padding: 10px 10px;"><strong>Duration:</strong></td>
                  <td style="padding: 10px 10px;">{{ $duration }}</td>
                </tr>
                <tr style="background-color: #f9f9f9;">
                  <td style="padding: 10px 10px;"><strong>Reason:</strong></td>
                  <td style="padding: 10px 10px;">{{ $reason }}</td>
                </tr>
              </table>

              <p style="color: #333333; font-size: 16px; margin-top: 20px;">Please review the request in the system for further action.</p>

              <p style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/leave-requests') }}" style="background-color: #0c0238; color: #ffffff; text-decoration: none; padding: 9px 18px; border-radius: 5px; font-weight: bold; font-size: 16px; display: inline-block;">View Leave Requests</a>
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #0c0238; text-align: center; padding: 20px; font-size: 14px; color: #fff; font-weight: 600;">
              Thank you,<br>
              ZENEROM Team
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
