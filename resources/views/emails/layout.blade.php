<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>@yield('title', 'Wefixit')</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:40px 0;">
    <tr>
      <td align="center">

        <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(15,23,42,0.08);">

          <!-- Top accent bar -->
          <tr>
            <td style="height:6px; background:linear-gradient(90deg,#2563eb,#7c3aed);"></td>
          </tr>

          <!-- Logo header -->
          <tr>
            <td align="center" style="padding:32px 40px 0 40px;">
              <div style="display:inline-flex; align-items:center; gap:8px;">
                <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,#2563eb,#7c3aed); display:flex; align-items:center; justify-content:center; font-weight:700; color:#ffffff; font-size:18px; line-height:36px; text-align:center;">W</div>
                <span style="font-size:20px; font-weight:700; color:#0f172a; vertical-align:middle;">Wefixit</span>
              </div>
            </td>
          </tr>

          @yield('content')

          <!-- Footer -->
          <tr>
            <td align="center" style="padding:24px 40px 32px 40px;">
              <p style="margin:0; font-size:12px; color:#cbd5e1;">© {{ date('Y') }} Wefixit. All rights reserved.</p>
            </td>
          </tr>

        </table>

      </td>
    </tr>
  </table>

</body>
</html>
