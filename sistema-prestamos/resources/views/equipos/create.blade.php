<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nuevo Equipo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form action="{{ route('equipos.store') }}" method="POST">
                        @csrf 

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Código Activo / Serie</label>
                                <input type="text" name="codigo_puce" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required placeholder="Ej: LAP-001">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tipo de Equipo</label>
                                <select name="tipo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Proyector">Proyector</option>
                                    <option value="Tablet">Tablet</option>
                                    <option value="Accesorio">Accesorio (Cargador, Mouse...)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Marca</label>
                                <input type="text" name="marca" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Modelo</label>
                                <input type="text" name="modelo" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Estado Inicial</label>
                                <select name="estado" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="disponible">Disponible</option>
                                    <option value="mantenimiento">En Mantenimiento</option>
                                    <option value="baja">De Baja</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Características (Procesador, RAM, Detalles)</label>
                                <textarea name="caracteristicas" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('dashboard') }}" class="text-gray-500 mr-4 hover:text-gray-700 pt-2">Cancelar</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Guardar Equipo
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>