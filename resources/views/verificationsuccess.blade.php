<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<Style>
.container-grid{
    position: relative;
    display: grid;
    height: 100vh;
    width: 100%;
    grid-template-areas: 
        "header"
    ;
    transition: .7s;
}

header {
    grid-area: header;
    padding-top: 10px;
    display: flex;
    height: 100%;
    flex-direction: column;
    text-align: center;
    justify-content: center;
    justify-items: center;
    align-items:center;
    align-content:center;
    gap:12px;
    font-size: 12px;
}

</Style>
<body>
    <div class="container-grid">
        <header>
            <img src="{{ asset('storage/appimage/wing.png') }}" style="width:400px;height:400px;">
            <h1 style="color:blue;">VERIFIKASI SUKSES</h1>
        </header>
    </div>
</body>
</html>