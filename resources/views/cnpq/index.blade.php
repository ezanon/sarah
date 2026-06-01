@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">🎖️ Nível de Pesquisador CNPq</h3>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('cnpq.store') }}" method="POST">
                @csrf
                <label class="form-label fw-bold mb-3">Selecione seu nível vigente:</label>
                <div class="row g-2 mb-3">
                    {{-- LOOP DOS NÍVEIS OFICIAIS --}}
                    @foreach(\App\Http\Controllers\CnpqController::NIVEIS as $key => $label)
                        <div class="col-md-6 col-lg-4 card p-2 border {{ $nivelAtual == $key ? 'border-primary bg-light' : '' }}" 
                             style="cursor: pointer;" onclick="document.getElementById('cnpq_{{ $key }}').click();">
                            <div class="d-flex align-items-center">
                                <input class="mr-2 me-2" type="radio" name="nivel_cnpq" value="{{ $key }}" id="cnpq_{{ $key }}"
                                    {{ $nivelAtual == $key ? 'checked' : '' }}>
                                <span class="fw-medium">{{ $label }}</span>
                            </div>
                        </div>
                    @endforeach

                    {{-- ITEM "NÃO POSSUO" --}}
                    <div class="col-md-6 col-lg-4 card p-2 border {{ $nivelAtual == '' ? 'border-primary bg-light' : '' }}" 
                         style="cursor: pointer;" onclick="document.getElementById('cnpq_null').click();">
                        <div class="d-flex align-items-center">
                            <input class="mr-2 me-2" type="radio" name="nivel_cnpq" value="" id="cnpq_null"
                                {{ $nivelAtual == '' ? 'checked' : '' }}>
                            <span class="fw-medium">Não possuo ou não informado</span>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary px-4">Salvar</button>
            </form>
        </div>
    </div>
</div>
@endsection