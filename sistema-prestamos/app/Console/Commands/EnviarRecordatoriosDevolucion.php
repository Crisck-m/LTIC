<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnviarRecordatoriosDevolucion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prestamos:enviar-recordatorios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar recordatorios de devolución de préstamos por email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $prestamos = \App\Models\Prestamo::where('estado', 'activo')
            ->where('notificar_retorno', true)
            ->where('recordatorio_enviado', false)
            ->whereNotNull('fecha_devolucion_esperada')
            ->get();

        $enviados = 0;

        foreach ($prestamos as $prestamo) {
            $fechaRecordatorio = $this->calcularFechaRecordatorio($prestamo);

            if (now()->isSameDay($fechaRecordatorio)) {
                \Illuminate\Support\Facades\Mail::to($prestamo->estudiante->email)
                    ->send(new \App\Mail\RecordatorioDevolucion($prestamo));

                $prestamo->update(['recordatorio_enviado' => true]);
                $enviados++;
                $this->info("Recordatorio enviado a {$prestamo->estudiante->email}");
            }
        }

        $this->info("Se enviaron {$enviados} recordatorios.");
    }

    private function calcularFechaRecordatorio($prestamo)
    {
        $fechaDevolucion = $prestamo->fecha_devolucion_esperada;

        return match ($prestamo->periodo_notificacion) {
            '1_dia' => $fechaDevolucion->copy()->subDay(),
            '1_semana' => $fechaDevolucion->copy()->subWeek(),
            '1_mes' => $fechaDevolucion->copy()->subMonth(),
            default => $fechaDevolucion->copy()->subDay(),
        };
    }
}
