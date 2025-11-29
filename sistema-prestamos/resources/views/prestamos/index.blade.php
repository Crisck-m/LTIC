<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Historial de Préstamos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">Listado General</h3>
                        <a href="{{ route('prestamos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + Nuevo Préstamo
                        </a>
                    </div>

                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="py-3 px-6">Equipo</th>
                                    <th class="py-3 px-6">Estudiante</th>
                                    <th class="py-3 px-6">Fecha Préstamo</th>
                                    <th class="py-3 px-6">Estado</th>
                                    <th class="py-3 px-6">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($prestamos as $prestamo)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="py-4 px-6">
                                            <div class="font-bold text-gray-900">{{ $prestamo->equipo->tipo }}</div>
                                            <div class="text-xs">{{ $prestamo->equipo->marca }} ({{ $prestamo->equipo->codigo_puce }})</div>
                                        </td>
                                        
                                        <td class="py-4 px-6">
                                            <div class="font-semibold text-gray-900">
                                                {{ $prestamo->estudiante->nombre }} {{ $prestamo->estudiante->apellido }}
                                            </div>
                                            <div class="text-xs text-gray-500">{{ $prestamo->estudiante->carrera }}</div>
                                        </td>

                                        <td class="py-4 px-6">
                                            {{ \Carbon\Carbon::parse($prestamo->fecha_prestamo)->format('d/m/Y h:i A') }}
                                            <br>
                                            <span class="text-xs text-gray-400">Reg: {{ $prestamo->responsable->name }}</span>
                                        </td>

                                        <td class="py-4 px-6">
                                            @if($prestamo->estado == 'activo')
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-yellow-400">
                                                    En Curso
                                                </span>
                                            @elseif($prestamo->estado == 'finalizado')
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded border border-green-400">
                                                    Devuelto
                                                </span>
                                            @else
                                                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                                    {{ $prestamo->estado }}
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-4 px-6">
                                            @if($prestamo->estado == 'activo')
                                                <button class="text-white bg-green-600 hover:bg-green-700 font-medium rounded-lg text-xs px-3 py-2 text-center">
                                                    <i class="fas fa-undo"></i> Devolver
                                                </button>
                                            @else
                                                <span class="text-gray-400 text-xs">Completado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-6 text-center text-gray-500">
                                            No hay préstamos registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $prestamos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>