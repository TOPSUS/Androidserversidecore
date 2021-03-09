<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TIRTA ARUNA COTTAGE</title>
    <style>
        table,th,td {
            text-align:center;
            background:#fff;
            padding:3px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="7" style="background:#1BA0DC;">
                <h1>
                ESPEEDBOAT<br>
                E-MAIL
                </h1>
                <P>
                Email Verifikasi Akun<br>
                </P>
            </th>
        </tr>
        
        <tr>
            <th colspan="7" >
                <p>Hai {{ $data['nama'] }}! Sudah siap berlayar >_< ? </p>
                <p>Tapi sebelum itu mohon akses link berikut ini untuk memverifikasi account Espeedboat anda</p>
            </th>
        </tr>
                <td colspan="7">
                    <a href="{{ $data['link'] }}">LINK VERIFIKASI ACCOUNT</a>
                </td>
        <tr>
            <td colspan="7" >
                <p></p>
            </td>
        </tr>
        
        <tr>
            <th colspan="7" style="background:#1BA0DC;">
                <h1 style="margin-bottom:20px;">Big Regrads</h1>
                <h1>TEAM TOPSUS TICKETING</h1>
            </th>
        </tr>
    </table>
</body>
</html>