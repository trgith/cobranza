<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<title>Notificacion de acceso</title>
	<style type="text/css">
		.card{
			margin: 20% 0;
		}
		.card-body{
			background-color: #FA8072;
		}
	</style>
</head>
<body>
	<div class="container">
		<div class="row">
			<div class="col">
				<div class="card text-center">
				  <div class="card-header">
				    <h4>Contraseña de acceso</h4>
				  </div>
				  <div class="card-body">
				    <h5 class="card-title">¡Buen dia!</h5>
				    <p class="card-text">A continuación le proporcionamos la contraseña de acceso al sistema de cobranza</p>
				    <button type="button" class="btn btn-outline-primary"><?= $_GET['clave'] ?></button>
				  </div>
				  <div class="card-footer text-muted">
				    <b>TR Network:</b> Cobranza
				  </div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>