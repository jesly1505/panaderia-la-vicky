<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - La Vicky</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background-color: #f4f6f9;
        }

        .sidebar {
            height: 100vh;
            background-color: #685569;
            padding-top: 20px;
            position: fixed;
            width: 16.666667%;
            overflow-y: auto;
        }

        .sidebar a {
            padding: 15px 20px;
            text-decoration: none;
            font-size: 16px;
            color: #d1d8e0;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
            color: #fff;
            background-color: #0d6efd;
        }

        .main-content {
            padding: 30px;
            margin-left: 16.666667%;
        }

        .top-navbar {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-left: 16.666667%;
        }

        .card-stat {
            border-radius: 10px;
            padding: 20px;
            color: #fff;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .bg-sales {
            background: linear-gradient(135deg, #FF9A9E 0%, #FECFEF 100%);
            color: #333;
        }

        .bg-orders {
            background: linear-gradient(135deg, #a18cd1 0%, #fbc2eb 100%);
        }

        .bg-products {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            color: #333;
        }

        .bg-customers {
            background: linear-gradient(135deg, #fccb90 0%, #d57eeb 100%);
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <?php $active = 'index'; $titulo = 'Resumen General'; include 'includes/sidebar.php'; ?>
        <!-- Dashboard Content -->
        <div class="main-content">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div id="lowStockAlertContainer"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card-stat bg-sales">
                                <h5>Ventas de Hoy</h5>
                                <h2 id="ventas-hoy">$0.00</h2>
                                <p class="mb-0"><i class="fas fa-arrow-up"></i> 0% vs ayer</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-stat bg-orders">
                                <h5>Pedidos Pendientes</h5>
                                <h2 id="pedidos-pendientes">0</h2>
                                <p class="mb-0"><i class="fas fa-clock"></i> Por entregar</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-stat bg-products">
                                <h5>Productos</h5>
                                <h2 id="productos-catalogo">0</h2>
                                <p class="mb-0"><i class="fas fa-check-circle"></i> En catálogo</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card-stat bg-customers">
                                <h5>Clientes</h5>
                                <h2 id="clientes-registrados">0</h2>
                                <p class="mb-0"><i class="fas fa-users"></i> Registrados</p>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                    <h5 class="card-title">Pedidos Pendientes</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Cliente</th>
                                                <th>Fecha</th>
                                                <th>Estado</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ultimos-pedidos-body">
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Cargando pedidos...</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                                    <h5 class="card-title">Alerta de Stock</h5>
                                </div>
                                <div class="card-body" id="alertas-stock-container">
                                    <div class="text-center text-muted">Cargando alertas...</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/app.js"></script>
</body>

</html>
