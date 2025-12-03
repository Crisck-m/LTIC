@extends('layouts.panel')

@section('titulo', 'Registrar Equipo')

@section('contenido')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 text-secondary">
                        <i class="fas fa-laptop-medical me-2"></i>Datos del Nuevo Equipo
                    </h5>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('equipos.store') }}" method="POST">
                        @csrf 

                        <div class="row g-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Identificación</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Código Activo / Serie <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-barcode"></i></span>
                                    <input type="text" name="codigo_puce" class="form-control" placeholder="Ej: LAP-001" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tipo de Equipo <span class="text-danger">*</span></label>
                                <select name="tipo" class="form-select" required>
                                    <option value="Laptop">Laptop</option>
                                    <option value="Proyector">Proyector</option>
                                    <option value="Tablet">Tablet</option>
                                    <option value="Accesorio">Accesorio (Cargador, Mouse...)</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="text-primary border-bottom pb-2 mb-3">Detalles Técnicos</h6>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Marca <span class="text-danger">*</span></label>
                                <input type="text" name="marca" class="form-control" placeholder="Ej: HP, Dell, Epson" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Modelo <span class="text-danger">*</span></label>
                                <input type="text" name="modelo" class="form-control" placeholder="Ej: Pavilion 15" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Estado Inicial</label>
                                <select name="estado" class="form-select">
                                    <option value="disponible">🟢 Disponible</option>
                                    <option value="mantenimiento">🔵 En Mantenimiento</option>
                                    <option value="baja">🔴 De Baja (Dañado)</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold">Características (Procesador, RAM, Detalles)</label>
                                <textarea name="caracteristicas" rows="3" class="form-control" placeholder="Ej: Core i7, 16GB RAM, SSD 512GB"></textarea>
                            </div>
                        </div>

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
@endsection