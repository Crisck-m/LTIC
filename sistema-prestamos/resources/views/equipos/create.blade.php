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

                        {{-- MOSTRAR ERRORES DE VALIDACIÓN --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>¡Error!</strong> Por favor corrige los siguientes problemas:
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('equipos.store') }}" method="POST">
                            @csrf

                            {{-- IDENTIFICACIÓN --}}
                            <h6 class="text-primary border-bottom pb-2 mb-3">Identificación</h6>

                            <div class="row g-3 mb-4">
                                {{-- TIPO DE EQUIPO --}}
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Tipo de Equipo <span
                                            class="text-danger">*</span></label>
                                    <select name="tipo" id="tipo" class="form-select @error('tipo') is-invalid @enderror"
                                        required onchange="toggleTipoOtro(); toggleCampoIndividual();">
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
                                    @error('tipo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- TIPO PERSONALIZADO (si es "Otro") --}}
                                <div class="col-md-6" id="tipoOtroContainer" style="display: none;">
                                    <label class="form-label fw-bold">Especifique el tipo <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="tipo_otro" id="tipo_otro"
                                        class="form-control @error('tipo_otro') is-invalid @enderror"
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
                                    @error('tipo_otro')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- NOMBRE/CÓDIGO (solo para Laptops) --}}
                                <div class="col-md-6" id="nombreContainer">
                                    <label class="form-label fw-bold" id="nombreLabel">Nombre / Código <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="nombre_equipo" id="nombre_equipo"
                                        class="form-control @error('nombre_equipo') is-invalid @enderror"
                                        placeholder="Ej: LAP-001" value="{{ old('nombre_equipo') }}">
                                    <small class="form-text text-muted" id="nombreHelp">Identificador único del
                                        equipo</small>
                                    @error('nombre_equipo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- CANTIDAD (solo para NO Laptops) --}}
                                <div class="col-md-6" id="cantidadContainer" style="display: none;">
                                    <label class="form-label fw-bold">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" name="cantidad_total" id="cantidad_total"
                                        class="form-control @error('cantidad_total') is-invalid @enderror" min="1"
                                        value="{{ old('cantidad_total', 1) }}" placeholder="Ej: 5">
                                    <small class="form-text text-muted">¿Cuántos equipos de este tipo estás
                                        registrando?</small>
                                    @error('cantidad_total')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- DETALLES TÉCNICOS (solo para Laptops) --}}
                            <div id="detallesTecnicosContainer">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Detalles Técnicos</h6>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Marca <span class="text-danger">*</span></label>
                                        <input type="text" name="marca" id="marca"
                                            class="form-control @error('marca') is-invalid @enderror"
                                            placeholder="Ej: HP, Dell, Epson" value="{{ old('marca') }}">
                                        @error('marca')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Modelo <span class="text-danger">*</span></label>
                                        <input type="text" name="modelo" id="modelo"
                                            class="form-control @error('modelo') is-invalid @enderror"
                                            placeholder="Ej: Pavilion 15" value="{{ old('modelo') }}">
                                        @error('modelo')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Estado Inicial</label>
                                        <select name="estado" class="form-select @error('estado') is-invalid @enderror"
                                            required>
                                            <option value="disponible" selected>🟢 Disponible</option>
                                            <option value="mantenimiento">🔵 Mantenimiento</option>
                                        </select>
                                        @error('estado')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-12">
                                        <label class="form-label fw-bold">Características (Procesador, RAM,
                                            Detalles)</label>
                                        <textarea name="caracteristicas" id="caracteristicas" rows="3"
                                            class="form-control @error('caracteristicas') is-invalid @enderror"
                                            placeholder="Ej: Core i7, 16GB RAM, SSD 512GB">{{ old('caracteristicas') }}</textarea>
                                        @error('caracteristicas')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- ESTADO SIMPLE (solo para equipos por cantidad) --}}
                            <div id="estadoSimpleContainer" style="display: none;">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Estado</h6>
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">Estado Inicial</label>
                                        <select name="estado_simple" id="estado_simple" class="form-select">
                                            <option value="disponible" selected>🟢 Disponible</option>
                                            <option value="mantenimiento">🔵 Mantenimiento</option>
                                        </select>
                                    </div>
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

            // Contenedores de detalles técnicos
            const detallesTecnicosContainer = document.getElementById('detallesTecnicosContainer');
            const estadoSimpleContainer = document.getElementById('estadoSimpleContainer');
            const marcaInput = document.getElementById('marca');
            const modeloInput = document.getElementById('modelo');
            const caracteristicasInput = document.getElementById('caracteristicas');

            if (tipo === 'Laptop') {
                // LAPTOP: Mostrar nombre, ocultar cantidad, mostrar todos los detalles técnicos
                nombreContainer.style.display = 'block';
                cantidadContainer.style.display = 'none';
                detallesTecnicosContainer.style.display = 'block';
                estadoSimpleContainer.style.display = 'none';

                nombreInput.required = true;
                cantidadInput.required = false;
                cantidadInput.value = 1;

                // Campos técnicos obligatorios para laptops
                marcaInput.required = true;
                modeloInput.required = true;
            } else if (tipo !== '') {
                // OTROS EQUIPOS: Ocultar nombre, mostrar cantidad, ocultar detalles técnicos
                nombreContainer.style.display = 'none';
                cantidadContainer.style.display = 'block';
                detallesTecnicosContainer.style.display = 'none';
                estadoSimpleContainer.style.display = 'block';

                nombreInput.required = false;
                nombreInput.value = '';
                cantidadInput.required = true;

                // Campos técnicos NO obligatorios para equipos por cantidad
                marcaInput.required = false;
                modeloInput.required = false;
                marcaInput.value = '';
                modeloInput.value = '';
                caracteristicasInput.value = '';
            } else {
                // Sin selección
                nombreContainer.style.display = 'block';
                cantidadContainer.style.display = 'none';
                detallesTecnicosContainer.style.display = 'block';
                estadoSimpleContainer.style.display = 'none';
            }
        }

        // Ejecutar al cargar
        document.addEventListener('DOMContentLoaded', function () {
            toggleTipoOtro();
            toggleCampoIndividual();

            // ===== VALIDACIÓN PRE-SUBMIT =====
            document.querySelector('form').addEventListener('submit', function (e) {
                let valid = true;
                const tipo = document.getElementById('tipo').value;

                // Función auxiliar: marcar error inline
                function setError(input, mensaje) {
                    input.classList.add('is-invalid');
                    let fb = input.nextElementSibling?.classList.contains('invalid-feedback-js')
                        ? input.nextElementSibling
                        : null;
                    if (!fb) {
                        fb = document.createElement('div');
                        fb.className = 'invalid-feedback-js invalid-feedback d-block';
                        fb.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i>${mensaje}`;
                        input.after(fb);
                    }
                }
                function clearError(input) {
                    input.classList.remove('is-invalid');
                    input.nextElementSibling?.classList.contains('invalid-feedback-js') && input.nextElementSibling.remove();
                }

                if (!tipo) {
                    valid = false;
                    setError(document.getElementById('tipo'), 'Debes seleccionar un tipo de equipo.');
                } else {
                    clearError(document.getElementById('tipo'));
                }

                if (tipo === 'Otro') {
                    const tipoOtro = document.getElementById('tipo_otro');
                    if (!tipoOtro.value.trim()) {
                        valid = false;
                        setError(tipoOtro, 'Debes especificar el tipo de equipo.');
                    } else {
                        clearError(tipoOtro);
                    }
                }

                if (tipo === 'Laptop') {
                    const nombreInput = document.getElementById('nombre_equipo');
                    const marcaInput = document.getElementById('marca');
                    const modeloInput = document.getElementById('modelo');

                    if (!nombreInput.value.trim()) { valid = false; setError(nombreInput, 'El nombre/código es obligatorio para laptops.'); }
                    else clearError(nombreInput);

                    if (!marcaInput.value.trim()) { valid = false; setError(marcaInput, 'La marca es obligatoria para laptops.'); }
                    else clearError(marcaInput);

                    if (!modeloInput.value.trim()) { valid = false; setError(modeloInput, 'El modelo es obligatorio para laptops.'); }
                    else clearError(modeloInput);

                } else if (tipo && tipo !== '') {
                    // Equipos por cantidad
                    const cantidadInput = document.getElementById('cantidad_total');
                    const cantidadVal = parseInt(cantidadInput.value, 10);
                    if (!cantidadInput.value || cantidadVal < 1 || isNaN(cantidadVal)) {
                        valid = false;
                        setError(cantidadInput, 'La cantidad debe ser al menos 1.');
                    } else {
                        clearError(cantidadInput);
                    }
                }

                if (!valid) {
                    e.preventDefault();
                    showToast('Por favor corrige los errores antes de guardar.', 'warning');
                    // Scroll al primer error
                    document.querySelector('.is-invalid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    </script>
@endsection