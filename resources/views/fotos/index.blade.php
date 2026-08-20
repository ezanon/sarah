@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-2">📷 Foto Alternativa</h3>
    <p class="text-muted mb-2">
        Envie uma foto recente. O sistema ajustará automaticamente para o formato <strong>3x4</strong> (padrão institucional).
    </p>
    <p class="text-muted mb-4">
        Esta foto poderá ser utilizada no website ou em relatórios, caso a permissão esteja ativa.
    </p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="a lert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white text-center">
                    👤 Foto Ativa no SARaH
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-4">
                    
                    @if($fotoCustom)
                        <img src="{{ $fotoCustom }}" alt="Minha Foto" 
                             class="img-fluid rounded shadow-sm mb-4" style="max-height: 320px; width: auto; object-fit: cover;">
                    @else
                        <div class="text-center text-muted py-5 mb-3 bg-light rounded w-100" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                            <div>
                                <i class="bi bi-cloud-upload fs-1 mb-2 d-block"></i>
                                <span>Nenhuma foto enviada ainda</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('foto.store') }}" method="POST" enctype="multipart/form-data" class="w-100 text-center">
                        @csrf
                        <label class="form-label fw-medium">Enviar nova foto (JPEG/PNG, máx 2MB)</label>
                        <input type="file" name="foto" class="form-control mb-3" accept="image/jpeg,image/png" required>
                        @error('foto') <div class="text-danger small mb-2">{{ $message }}</div> @enderror
                        <button type="submit" class="btn btn-primary px-4">Salvar e Ajustar para 3x4</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection