@extends('layouts.panel')

@section('titulo', 'Registrar Equipo')

@section('contenido')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-secondary">
                            <i class="fas fa-laptop me-2"></i>Datos del Nuevo Equipo
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        <form action="{{ route('equipos.store') }}" method="POST">
                            @csrf

                            {{-- IDENTIFICACIÓN --}}
                            <h6 class="text-primary border-bottom pb-2 mb-3">Identificación</h6>

                            <div class="row g-3 mb-4">
                                {{-- TIPO DE EQUIPO --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tipo de Equipo <span
                                            class="text-danger">*</span></label>
                                    <select name="tipo" id="tipo" class="form-select" required
                                        onchange="toggleTipoOtro(); toggleCampoIndividual();">
                                        <option value="" disabled selected>Seleccione un tipo...</option>
                                        <option value="Laptop" {{ old('tipo') == 'Laptop' ? 'selected' : '' }}>💻 Laptop
                                        </option>
                                        <option value="Mouse" {{ old('tipo') == 'Mouse' ? 'selected' : '' }}>🖱️ Mouse
                                        </option>
                                        <option value="Cable de red" {{ old('tipo') == 'Cable de red' ? 'selected' : '' }}>🔌
                                            Cable de red</option>
                                        <option value="Cable HDMI" {{ old('tipo') == 'Cable HDMI' ? 'selected' : '' }}>📺
                                            Cable HDMI</option>
                                        <option value="Otro" {{ old('tipo') == 'Otro' ? 'selected' : '' }}>📦 Otro</option>
                                    </select>
                                </div>

                                {{-- TIPO PERSONALIZADO (si es "Otro") --}}
                                <div class="col-md-6" id="tipoOtroContainer" style="display: none;">
                                    <label class="form-label fw-bold">Especifique el tipo <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="tipo_otro" id="tipo_otro" class="form-control"
                                        placeholder="Ej: Proyector, Router..." value="{{ old('tipo_otro') }}"
                                        list="tiposPersonalizadosSugeridos">
                                    <datalist id="tiposPersonalizadosSugeridos">
                                        <option value="Proyector">
                                        <option value="Router">
                                        <option value="Switch">
                                        <option value="Cámara">
                                        <option value="Teclado">
                                        <option value="Parlantes">
                                    </datalist>
                                </div>

                                {{-- NOMBRE/CÓDIGO (solo para Laptops) --}}
                                <div class="col-md-6" id="nombreContainer">
                                    <label class="form-label fw-bold" id="nombreLabel">Nombre / Código <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="nombre_equipo" id="nombre_equipo" class="form-control"
                                        placeholder="Ej: LAP-001" value="{{ old('nombre_equipo') }}">
                                    <small class="form-text text-muted" id="nombreHelp">Identificador único del
                                        equipo</small>
                                </div>

                                {{-- CANTIDAD (solo para NO Laptops) --}}
                                <div class="col-md-6" id="cantidadContainer" style="display: none;">
                                    <label class="form-label fw-bold">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" name="cantidad_total" id="cantidad_total" class="form-control"
                                        min="1" value="{{ old('cantidad_total', 1) }}" placeholder="Ej: 5">
                                    <small class="form-text text-muted">¿Cuántos equipos de este tipo estás
                                        registrando?</small>
                                </div>
                            </div>

                            {{-- DETALLES TÉCNICOS --}}
                            <h6 class="text-primary border-bottom pb-2 mb-3">Detalles Técnicos</h6>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Marca <span class="text-danger">*</span></label>
                                    <input type="text" name="marca" class="form-control" placeholder="Ej: HP, Dell, Epson"
                                        value="{{ old('marca') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Modelo <span class="text-danger">*</span></label>
                                    <input type="text" name="modelo" class="form-control" placeholder="Ej: Pavilion 15"
                                        value="{{ old('modelo') }}" required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Estado Inicial</label>
                                    <select name="estado" class="form-select" required>
                                        <option value="disponible" selected>🟢 Disponible</option>
                                        <option value="mantenimiento">🔵 Mantenimiento</option>
                                    </select>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label fw-bold">Características (Procesador, RAM, Detalles)</label>
                                    <textarea name="caracteristicas" rows="3" class="form-control"
                                        placeholder="Ej: Core i7, 16GB RAM, SSD 512GB">{{ old('caracteristicas') }}</textarea>
                                </div>
                            </div>

                            {{-- BOTONES --}}
                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('equipos.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-2"></i>Guardar Equipo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleTipoOtro() {
            const select = document.getElementById('tipo');
            const container = document.getElementById('tipoOtroContainer');
            const input = document.getElementById('tipo_otro');

            if (select.value === 'Otro') {
                container.style.display = 'block';
                input.required = true;
            } else {
                container.style.display = 'none';
                input.required = false;
                input.value = '';
            }
        }

        function toggleCampoIndividual() {
            const tipo = document.getElementById('tipo').value;
            const nombreContainer = document.getElementById('nombreContainer');
            const cantidadContainer = document.getElementById('cantidadContainer');
            const nombreInput = document.getElementById('nombre_equipo');
            const cantidadInput = document.getElementById('cantidad_total');

            if (tipo === 'Laptop') {
                // LAPTOP: Mostrar nombre, ocultar cantidad
                nombreContainer.style.display = 'block';
                cantidadContainer.style.display = 'none';
                nombreInput.required = true;
                cantidadInput.required = false;
                cantidadInput.value = 1;
            } else if (tipo !== '') {
                // OTROS: Ocultar nombre, mostrar cantidad
                nombreContainer.style.display = 'none';
                cantidadContainer.style.display = 'block';
                nombreInput.required = false;
                nombreInput.value = '';
                cantidadInput.required = true;
            } else {
                // Sin selección
                nombreContainer.style.display = 'block';
                cantidadContainer.style.display = 'none';
            }
        }

        // Ejecutar al cargar
        document.addEventListener('DOMContentLoaded', function () {
            toggleTipoOtro();
            toggleCampoIndividual();
        });
    </script>
@endsection