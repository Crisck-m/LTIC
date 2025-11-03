<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Sistema de Préstamos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 15px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
            color: #ffffff;
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background-color: #e74c3c;
            color: white;
        }
        .sidebar .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #3498db;
            transition: transform 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card.primary { border-left-color: #3498db; }
        .stat-card.success { border-left-color: #2ecc71; }
        .stat-card.warning { border-left-color: #f39c12; }
        .stat-card.danger { border-left-color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">
                            <i class="fas fa-laptop-house"></i>
                            Sistema Préstamos
                        </h4>
                        <small class="text-light">LTIC</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('dashboard') }}">
                                <i class="fas fa-home"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-users"></i> Estudiantes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-laptop"></i> Inventario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-hand-holding"></i> Préstamos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-undo"></i> Devoluciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-bar"></i> Reportes
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i> Configuración
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4" style="background-color: #f8f9fa; min-height: 100vh;">
                <!-- Header -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">
                        <i class="fas fa-home text-primary"></i>
                        Home
                    </h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i> {{ Auth::user()->name }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Perfil</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Configuración</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card primary">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted mb-0">Estudiantes Registrados</h5>
                                    <h2 class="mt-2 mb-0">0</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card primary">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted mb-0">Equipos Disponibles</h5>
                                    <h2 class="mt-2 mb-0">0</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-laptop fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card primary">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted mb-0">Préstamos Activos</h5>
                                    <h2 class="mt-2 mb-0">0</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-hand-holding fa-2x text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="stat-card primary">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title text-muted mb-0">Pendientes Devolución</h5>
                                    <h2 class="mt-2 mb-0">0</h2>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt text-warning me-2"></i>
                                    Acciones Rápidas
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-lg-3 col-md-6">
                                        <a href="#" class="btn btn-primary w-100 h-100 py-3">
                                            <i class="fas fa-user-plus fa-2x mb-2"></i><br>
                                            Registrar Estudiante
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <a href="#" class="btn btn-primary w-100 h-100 py-3">
                                            <i class="fas fa-laptop fa-2x mb-2"></i><br>
                                            Agregar Equipo
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <a href="#" class="btn btn-primary w-100 h-100 py-3">
                                            <i class="fas fa-hand-holding fa-2x mb-2"></i><br>
                                            Nuevo Préstamo
                                        </a>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <a href="#" class="btn btn-primary w-100 h-100 py-3">
                                            <i class="fas fa-undo fa-2x mb-2"></i><br>
                                            Registrar Devolución
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="row mt-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history text-primary me-2"></i>
                                    Actividad Reciente
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-hand-holding text-warning me-2"></i>
                                            <strong>Juan Pérez</strong> solicitó una laptop
                                        </div>
                                        <small class="text-muted">Hace 5 minutos</small>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-undo text-success me-2"></i>
                                            <strong>María García</strong> devolvió un equipo
                                        </div>
                                        <small class="text-muted">Hace 1 hora</small>
                                    </div>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="fas fa-user-plus text-primary me-2"></i>
                                            Nuevo estudiante registrado
                                        </div>
                                        <small class="text-muted">Hace 2 horas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-info-circle text-info me-2"></i>
                                    Información del Sistema
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted">Versión</small>
                                    <div>1.0.0</div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Última actualización</small>
                                    <div>{{ now()->format('d/m/Y') }}</div>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted">Estado</small>
                                    <div><span class="badge bg-success">Operativo</span></div>
                                </div>
                                <div>
                                    <small class="text-muted">Soporte</small>
                                    <div>soporte@ltic.edu</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Activar tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    </script>
</body>
</html>