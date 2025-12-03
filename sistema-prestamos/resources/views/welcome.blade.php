<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Préstamos LTIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .welcome-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 900px;
            width: 90%;
        }
        .left-side {
            background: linear-gradient(180deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        .right-side {
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .btn-custom {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s;
            width: 100%;
            margin-bottom: 15px;
        }
        .btn-login {
            background-color: #3498db;
            color: white;
            border: none;
        }
        .btn-login:hover {
            background-color: #2980b9;
            color: white;
            transform: translateY(-2px);
        }
        .btn-register {
            background-color: white;
            color: #3498db;
            border: 2px solid #3498db;
        }
        .btn-register:hover {
            background-color: #f8f9fa;
            color: #2980b9;
        }
    </style>
</head>
<body>

    <div class="welcome-card">
        <div class="row g-0">
            <div class="col-md-6 left-side">
                <i class="fas fa-laptop-house fa-5x mb-4"></i>
                <h2 class="fw-bold mb-3">Sistema de Préstamos</h2>
                <h4 class="mb-4">LTIC - PUCE</h4>
                <p class="opacity-75">Gestión eficiente de inventario, préstamos y devoluciones de equipos tecnológicos.</p>
            </div>

            <div class="col-md-6 right-side">
                <div class="text-center mb-5">
                    <h3 class="fw-bold text-primary">PUCE TEC</h3>
                </div>

                <h3 class="text-center mb-4 fw-bold text-secondary">Bienvenido</h3>

                @if (Route::has('login'))
                    <div class="d-grid gap-2">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-custom btn-login">
                                <i class="fas fa-tachometer-alt me-2"></i> Ir al Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-custom btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-custom btn-register">
                                    <i class="fas fa-user-plus me-2"></i> Registrarse
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
                
                <div class="text-center mt-4">
                    <small class="text-muted">&copy; {{ date('Y') }} PUCE TEC - Desarrollo de Software</small>
                </div>
            </div>
        </div>
    </div>

</body>
</html>