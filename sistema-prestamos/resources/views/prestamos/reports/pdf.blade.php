<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Préstamos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #4472C4;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #4472C4;
            font-size: 20px;
            margin-bottom: 5px;
        }

        .header h2 {
            color: #666;
            font-size: 14px;
            font-weight: normal;
        }

        .metadata {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #4472C4;
            font-size: 9px;
        }

        .metadata p {
            margin: 3px 0;
        }

        .metadata strong {
            color: #4472C4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 8px;
        }

        table thead {
            background-color: #4472C4;
            color: white;
        }

        table thead th {
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }

        table tbody td {
            padding: 6px 4px;
            border: 1px solid #dee2e6;
            vertical-align: top;
        }

        table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        table tbody tr:hover {
            background-color: #e9ecef;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #333;
        }

        .badge-danger {
            background-color: #dc3545;
            color: white;
        }

        .badge-info {
            background-color: #17a2b8;
            color: white;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #4472C4;
            font-size: 8px;
        }

        .estadisticas {
            background-color: #f8f9fa;
            padding: 10px;
            margin-top: 15px;
            border-radius: 5px;
        }

        .estadisticas h3 {
            color: #4472C4;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .estadisticas p {
            margin: 4px 0;
            font-size: 9px;
        }

        .small-text {
            font-size: 7px;
            color: #666;
        }

        .text-center {
            text-align: center;
        }

        .no-data {
            text-align: center;
            padding: 30px;
            color: #999;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Sistema de Préstamos LTIC</h1>
        <h2>Reporte de Préstamos</h2>
    </div>

    <div class="metadata">
        <p><strong>Fecha de Generación:</strong> {{ $fechaGeneracion }}</p>
        <p><strong>Hora de Generación:</strong> {{ $horaGeneracion }}</p>
        <p><strong>Generado por:</strong> {{ $rolUsuario }}</p>

        @if($filtrosAplicados)
            <p><strong>Filtros Aplicados:</strong></p>
            @if(isset($filtros['search']) && $filtros['search'])
                <p style="margin-left: 15px;">• Búsqueda: "{{ $filtros['search'] }}"</p>
            @endif
            @if(isset($filtros['estado']) && $filtros['estado'])
                <p style="margin-left: 15px;">• Estado: {{ $filtros['estado'] == 'activo' ? 'En Curso' : 'Devueltos' }}</p>
            @endif
            @if(isset($filtros['fecha_desde']) && $filtros['fecha_desde'])
                <p style="margin-left: 15px;">• Desde: {{ \Carbon\Carbon::parse($filtros['fecha_desde'])->format('d/m/Y') }}</p>
            @endif
            @if(isset($filtros['fecha_hasta']) && $filtros['fecha_hasta'])
                <p style="margin-left: 15px;">• Hasta: {{ \Carbon\Carbon::parse($filtros['fecha_hasta'])->format('d/m/Y') }}</p>
            @endif
        @else
            <p><strong>Filtros Aplicados:</strong> Sin filtros (Reporte completo)</p>
        @endif

        <p><strong>Total de Registros:</strong> {{ count($prestamos) }} préstamo(s)</p>
    </div>

    @if(count($prestamos) > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Equipo</th>
                    <th style="width: 14%;">Estudiante</th>
                    <th style="width: 12%;">Practicante</th>
                    <th style="width: 12%;">Fecha Préstamo</th>
                    <th style="width: 10%;">Fecha Esperada</th>
                    <th style="width: 12%;">Fecha Real</th>
                    <th style="width: 8%;">Estado</th>
                    <th style="width: 10%;">Cumplimiento</th>
                    <th style="width: 10%;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prestamos as $prestamo)
                    @php
                        $cumplimiento = 'Pendiente';
                        $badgeClass = 'badge-info';

                        if ($prestamo->estado == 'finalizado' && $prestamo->fecha_devolucion_real) {
                            $fechaReal = \Carbon\Carbon::parse($prestamo->fecha_devolucion_real)->startOfDay();
                            $fechaEsperada = \Carbon\Carbon::parse($prestamo->fecha_devolucion_esperada)->startOfDay();

                            if ($fechaReal->lte($fechaEsperada)) {
                                $cumplimiento = 'A tiempo';
                                $badgeClass = 'badge-success';
                            } else {
                                $diasRetraso = $fechaEsperada->diffInDays($fechaReal);
                                $cumplimiento = "Retraso +{$diasRetraso}d";
                                $badgeClass = 'badge-danger';
                            }
                        }
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $prestamo->equipo->tipo ?? '-' }}</strong><br>
                            <span class="small-text">{{ $prestamo->equipo->marca ?? '-' }}
                                {{ $prestamo->equipo->modelo ?? '-' }}</span><br>
                            <span class="small-text">{{ $prestamo->equipo->nombre_equipo ?? '-' }}</span>
                        </td>
                        <td>
                            <strong>{{ $prestamo->estudiante->nombre ?? '' }}
                                {{ $prestamo->estudiante->apellido ?? '' }}</strong><br>
                            <span class="small-text">{{ $prestamo->estudiante->carrera ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="small-text">Registra:</span><br>
                            {{ $prestamo->practicante->nombre ?? '' }} {{ $prestamo->practicante->apellido ?? '' }}
                            @if($prestamo->practicanteDevolucion)
                                <br><span class="small-text">Recibe:</span><br>
                                {{ $prestamo->practicanteDevolucion->nombre }} {{ $prestamo->practicanteDevolucion->apellido }}
                            @endif
                        </td>
                        <td class="small-text">
                            {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y') }}<br>
                            {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('H:i A') }}
                        </td>
                        <td class="small-text">
                            {{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_esperada)->format('d/m/Y') }}
                        </td>
                        <td class="small-text">
                            @if($prestamo->fecha_devolucion_real)
                                {{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_real)->format('d/m/Y') }}<br>
                                {{ \Carbon\Carbon::parse($prestamo->fecha_devolucion_real)->format('H:i A') }}
                            @else
                                <em>Pendiente</em>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $prestamo->estado == 'activo' ? 'badge-warning' : 'badge-success' }}">
                                {{ $prestamo->estado == 'activo' ? 'En Curso' : 'Devuelto' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $badgeClass }}">{{ $cumplimiento }}</span>
                        </td>
                        <td class="small-text">
                            @if($prestamo->observaciones_prestamo)
                                <strong>Préstamo:</strong> {{ Str::limit($prestamo->observaciones_prestamo, 50) }}<br>
                            @endif
                            @if($prestamo->observaciones_devolucion)
                                <strong>Devolución:</strong> {{ Str::limit($prestamo->observaciones_devolucion, 50) }}
                            @endif
                            @if(!$prestamo->observaciones_prestamo && !$prestamo->observaciones_devolucion)
                                <em>Sin observaciones</em>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="estadisticas">
            <h3>Resumen Estadístico</h3>
            <p><strong>Total de Préstamos:</strong> {{ $estadisticas['total'] ?? count($prestamos) }}</p>
            <p><strong>Préstamos Activos:</strong> {{ $estadisticas['activos'] ?? 0 }}</p>
            <p><strong>Préstamos Finalizados:</strong> {{ $estadisticas['finalizados'] ?? 0 }}</p>
            <p><strong>Devueltos a Tiempo:</strong> {{ $estadisticas['a_tiempo'] ?? 0 }}</p>
            <p><strong>Devueltos con Retraso:</strong> {{ $estadisticas['con_retraso'] ?? 0 }}</p>
        </div>
    @else
        <div class="no-data">
            <p>No se encontraron préstamos que coincidan con los filtros aplicados.</p>
        </div>
    @endif

    <div class="footer">
        <p class="text-center">Sistema de Préstamos LTIC - Documento generado automáticamente el {{ $fechaGeneracion }}
            a las {{ $horaGeneracion }}</p>
    </div>
</body>

</html>