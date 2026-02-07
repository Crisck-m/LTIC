<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Login - Sistema de Préstamos LTIC</title>
    <link rel="icon" type="image/png" href="{{ asset('images/LogoRecorLTIC.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --puce-blue: #003DA5;
            --puce-light-blue: #0066CC;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .login-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.4);
        }

        .puce-button {
            background: var(--puce-blue);
            transition: all 0.3s ease;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .puce-button:hover {
            background: var(--puce-light-blue);
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 61, 165, 0.4);
        }

        .input-field {
            transition: all 0.3s ease;
            border: 2px solid #e5e7eb;
        }

        .input-field:focus {
            border-color: var(--puce-blue);
            box-shadow: 0 0 0 3px rgba(0, 61, 165, 0.1);
            outline: none;
        }

        .logo-container {
            animation: fadeInDown 0.8s ease;
        }

        .form-container {
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4">
        <!-- Logo -->
        <div class="logo-container mb-8">
            <img src="{{ asset('images/logo.png') }}" alt="PUCE Logo" class="h-24 w-auto mx-auto drop-shadow-2xl">
        </div>

        <!-- Card de Login -->
        <div class="w-full sm:max-w-md form-container">
            <div class="login-card px-8 py-10 rounded-2xl">
                <!-- Título -->
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Bienvenido</h2>
                    <p class="text-gray-600 mt-2">Sistema de Préstamos LTIC</p>
                </div>

                {{ $slot }}
            </div>

            <!-- Footer -->
            <div class="text-center mt-6">
                <p class="text-white text-sm drop-shadow">
                    © {{ date('Y') }} Pontificia Universidad Católica del Ecuador
                </p>
            </div>
        </div>
    </div>
</body>

</html>