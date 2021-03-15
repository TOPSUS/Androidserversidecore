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
    height: 100vw;
    width: 100%;
    grid-template-areas: 
        "header"
        "section"
    ;
    grid-template-rows: 50% 50%;
    transition: .7s;
}

header {
    grid-area: header;
    padding-top: 10px;
    display: flex;
    height: 100%;
    flex-direction: column;
    text-align: center;
    gap:12px;
    font-size: 12px;
    
}

</Style>
<body>
    <div class="container-grid">
        <header>
            <img src="{{ asset('storage/appimage/wing.png') }}" alt="">
        </header>
    </div>
</body>
</html>