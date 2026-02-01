<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Recordatorio de Devolución</title>
</head>

<body>
    <h1>Recordatorio: Devolución de Equipo</h1>

    <p>Hola {{ $prestamo->estudiante->nombre }} {{ $prestamo->estudiante->apellido }},</p>

    <p>Te recordamos que tienes un equipo prestado que debe ser devuelto próximamente.</p>

    <ul>
        <li><strong>Equipo:</strong> {{ $prestamo->equipo->tipo }} - {{ $prestamo->equipo->marca }} (Serie:
            {{ $prestamo->equipo->nombre_equipo }})</li>
        <li><strong>Fecha de Préstamo:</strong> {{ $prestamo->fecha_prestamo->format('d/m/Y H:i') }}</li>
        <li><strong>Fecha Esperada de Devolución:</strong>
            {{ $prestamo->fecha_devolucion_esperada ? $prestamo->fecha_devolucion_esperada->format('d/m/Y H:i') : 'No especificada' }}
        </li>
    </ul>

    <p>Por favor, devuelve el equipo al laboratorio LTIC lo antes posible para evitar inconvenientes.</p>

    <p>Si ya has devuelto el equipo, ignora este mensaje.</p>

    <p>Atentamente,<br>
        Equipo LTIC</p>
</body>

</html>