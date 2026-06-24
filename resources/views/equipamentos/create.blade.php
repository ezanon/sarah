@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h3 class="mb-4">➕➕Cadastrar Novo Equipamento</h3>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('equipamentos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('equipamentos.form')
                <div class="d-flex justify-content-end gap-2 mt-4">
                    <a href="{{ route('equipamentos.index') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary ml-1">Salvar Equipamento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection