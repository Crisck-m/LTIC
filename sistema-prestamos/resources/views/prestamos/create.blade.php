<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Préstamo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form action="{{ route('prestamos.store') }}" method="POST">
                        @csrf 

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estudiante que solicita</label>
                                <select name="estudiante_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="" disabled selected>Seleccione un estudiante...</option>
                                    @foreach($estudiantes as $estudiante)
                                        <option value="{{ $estudiante->id }}">
                                            {{ $estudiante->nombre }} {{ $estudiante->apellido }} - {{ $estudiante->carrera }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Equipo a Prestar (Disponibles)</label>
                                <select name="equipo_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="" disabled selected>Seleccione un equipo...</option>
                                    @foreach($equipos as $equipo)
                                        <option value="{{ $equipo->id }}">
                                            {{ $equipo->tipo }} - {{ $equipo->marca }} ({{ $equipo->codigo_puce }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($equipos->isEmpty())
                                    <p class="text-red-500 text-xs mt-1">No hay equipos disponibles en este momento.</p>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Responsable del registro</label>
                                <input type="text" value="{{ Auth::user()->name }}" disabled class="mt-1 block w-full bg-gray-100 rounded-md border-gray-300 shadow-sm">
                                <p class="text-xs text-gray-500">Se registrará tu usuario automáticamente.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Observaciones de entrega</label>
                                <textarea name="observaciones" rows="1" placeholder="Ej: Se entrega con cargador y mouse..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('dashboard') }}" class="text-gray-500 mr-4 hover:text-gray-700 pt-2">Cancelar</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Confirmar Préstamo
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>