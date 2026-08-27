@extends('emails.layout')

@section('title', 'Wefixit - Verify Your Email')

@section('content')
          <!-- Body -->
          <tr>
            <td style="padding:32px 40px 8px 40px;" align="center">
              <h1 style="margin:0 0 12px 0; font-size:22px; color:#0f172a; font-weight:700;">Verify your email</h1>
              <p style="margin:0; font-size:15px; line-height:1.6; color:#64748b; max-width:360px;">
                Welcome aboard! Use the code below to confirm your email address and finish setting up your account.
              </p>
            </td>
          </tr>

          <!-- OTP Box -->
          <tr>
            <td align="center" style="padding:28px 40px 8px 40px;">
              <table role="presentation" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="background:linear-gradient(135deg,#eff6ff,#f5f3ff); border:1px solid #dbeafe; border-radius:14px; padding:20px 36px;">
                    <span style="font-size:32px; font-weight:700; letter-spacing:10px; color:#1e293b; font-family:'Courier New',monospace;">{{ $otp }}</span>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Expiry notice -->
          <tr>
            <td align="center" style="padding:16px 40px 0 40px;">
              <table role="presentation" cellpadding="0" cellspacing="0" style="background-color:#fff7ed; border-radius:8px;">
                <tr>
                  <td style="padding:10px 16px; font-size:13px; color:#c2410c; font-weight:600;">
                    ⏱ Expires in 5 minutes
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Security note -->
          <tr>
            <td style="padding:32px 40px 0 40px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="border-top:1px solid #e2e8f0; padding-top:20px; font-size:12.5px; line-height:1.6; color:#94a3b8;" align="center">
                    Didn't request this code? You can safely ignore this email — no changes will be made to your account.
                    <br>Never share this code with anyone, including Wefixit staff.
                  </td>
                </tr>
              </table>
            </td>
          </tr>
@endsection
