@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">✏️ Editar Equipamento: {{ $equipamento->nome }}</h3>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('equipamentos.update', $equipamento) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('equipamentos.form')
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('equipamentos.show', $equipamento) }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success ml-1">Atualizar Equipamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection