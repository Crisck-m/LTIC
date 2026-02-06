<?php

namespace App\Console\Commands;

use App\Models\Prestamo;
use App\Models\PrestamoEquipo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrarPrestamosANuevaEstructura extends Command
{
    protected $signature = 'prestamos:migrar';
    protected $description = 'Migrar préstamos existentes a la nueva estructura con tabla intermedia';

    public function handle()
    {
        $this->info('Iniciando migración de préstamos...');

        DB::transaction(function () {
            $prestamos = Prestamo::whereNotNull('equipo_id')->get();

            $this->info("Encontrados {$prestamos->count()} préstamos para migrar.");

            $barra = $this->output->createProgressBar($prestamos->count());

            foreach ($prestamos as $prestamo) {
                // Crear registro en prestamo_equipos
                PrestamoEquipo::create([
                    'prestamo_id' => $prestamo->id,
                    'equipo_id' => $prestamo->equipo_id,
                    'fecha_devolucion_real' => $prestamo->fecha_devolucion_real,
                    'practicante_recibe_id' => $prestamo->practicante_recibe_id ?? null,
                    'observaciones_devolucion' => $prestamo->observaciones_devolucion,
                    'estado' => $prestamo->estado === 'finalizado' ? 'devuelto' : 'activo',
                ]);

                $barra->advance();
            }

            $barra->finish();
        });

        $this->newLine();
        $this->info('✅ Migración completada exitosamente.');
        $this->info('📊 Total de equipos migrados: ' . PrestamoEquipo::count());
    }
}