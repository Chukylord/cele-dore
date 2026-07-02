@extends('layouts.admin')

@section('title', 'Editar Gasto - Cele Dore Estilista')
@section('h1', 'Editar Gasto')
@section('sub', 'Modificá la información del gasto seleccionado.')

@section('content')
    @if($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <div class="font-semibold mb-1">Hay errores:</div>
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('gastos.update', $gasto) }}">
        @method('PUT')
        @include('gastos._form', ['gasto' => $gasto])
    </form>
@endsection