<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Inventario de Equipos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="flex justify-between mb-4">
                        <h3 class="text-lg font-bold">Listado de Equipos</h3>
                        <a href="{{ route('equipos.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + Agregar Nuevo Equipo
                        </a>
                    </div>

                    <div class="overflow-x-auto relative">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 px-6">Código</th>
                                    <th scope="col" class="py-3 px-6">Tipo</th>
                                    <th scope="col" class="py-3 px-6">Marca / Modelo</th>
                                    <th scope="col" class="py-3 px-6">Estado</th>
                                    <th scope="col" class="py-3 px-6">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($equipos as $equipo)
                                    <tr class="bg-white border-b">
                                        <td class="py-4 px-6 font-medium text-gray-900 whitespace-nowrap">
                                            {{ $equipo->codigo_puce }}
                                        </td>
                                        <td class="py-4 px-6">
                                            {{ $equipo->tipo }}
                                        </td>
                                        <td class="py-4 px-6">
                                            {{ $equipo->marca }} - {{ $equipo->modelo }}
                                        </td>
                                        <td class="py-4 px-6">
                                            @if($equipo->estado == 'disponible')
                                                <span class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">Disponible</span>
                                            @elseif($equipo->estado == 'prestado')
                                                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">Prestado</span>
                                            @else
                                                <span class="bg-red-100 text-red-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded">{{ ucfirst($equipo->estado) }}</span>
                                            @endif
                                        </td>
                                        <td class="py-4 px-6">
                                            <a href="#" class="font-medium text-blue-600 hover:underline">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-4 px-6 text-center text-gray-500">
                                            No hay equipos registrados aún.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $equipos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>