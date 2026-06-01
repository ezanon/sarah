@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-2">🌍 Objetivos de Desenvolvimento Sustentável (ODS)</h3>
    <p class="text-muted mb-4">Selecione as ODS que seu trabalho ou pesquisa contribui diretamente.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('ods.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            @foreach(\App\Http\Controllers\OdsController::ODS_LIST as $id => $info)
                <div class="col-md-4 col-lg-3">
                    <label class="card h-100 border shadow-sm {{ in_array($id, $minhasOds) ? 'border-primary' : '' }}" 
                           for="ods_{{ $id }}" style="cursor: pointer;">
                        <div class="card-body text-center p-3">
                            <img src="{{ asset($info['img']) }}" alt="ODS {{ $id }}" class="img-fluid mb-2" style="max-height: 90px; width: auto;">
                            <p class="card-text small fw-bold mt-1 mb-0">{{ $info['nome'] }}</p>
                            
                            <div class="form-check mt-1 mb-1 d-flex justify-content-center">
                                <input class="form-check-input" type="checkbox" name="ods[]" value="{{ $id }}" id="ods_{{ $id }}"
                                       {{ in_array($id, $minhasOds) ? 'checked' : '' }}>
                            </div>
                        </div>
                    </label>
                </div>
            @endforeach
        </div>
        <button type="submit" class="btn btn-primary mt-4 px-4">Salvar Seleção</button>
    </form>
</div>
@endsection