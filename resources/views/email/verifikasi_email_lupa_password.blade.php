<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESPEED BOAT ANDROID SISTEM</title>
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
                Lupa Password<br>
                </P>
            </th>
        </tr>
        
        <tr>
            <th colspan="7" >
                <p>Hai {{ $data['nama'] }}!, lupa password ya ?<br> >_<</p>
                <p>berikut ini merupakan kode verifikasi email anda :</p>
                <p style="background:orange;color:white;">{{ $data['kode_verifikasi'] }}</p>
                <p>Silahkan masukkan kode diatas ke dalam halaman verifikasi pada aplikasi android</p>
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
                <small style="margin-bottom:20px;">Big Regrads</small><br><br>
                <small>---TEAM TOPSUS TICKETING---</small>
            </th>
        </tr>
    </table>
</body>
</html>