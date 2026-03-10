@extends('layouts.admin')

@section('title', 'Nuevo Producto - Peluquería TOP')
@section('h1', 'Nuevo Producto')
@section('sub', 'Registrar un producto para ventas y stock.')

@section('content')

    @if(session('dup_producto'))
        <div class="mb-4 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-900">
            <div class="font-semibold">Este producto ya existe (mismo proveedor, marca, tipo y contenido).</div>
            <div class="text-sm mt-1">¿Querés crearlo igual?</div>

            <form method="POST" action="{{ route('productos.store') }}" class="mt-3">
                @csrf

                <input type="hidden" name="proveedor_id" value="{{ old('proveedor_id') }}">
                <input type="hidden" name="marca" value="{{ old('marca') }}">
                <input type="hidden" name="tipo" value="{{ old('tipo') }}">
                <input type="hidden" name="contenido" value="{{ old('contenido') }}">
                <input type="hidden" name="codigo_barra" value="{{ old('codigo_barra') }}">
                <input type="hidden" name="precio_venta" value="{{ old('precio_venta') }}">
                <input type="hidden" name="stock_venta" value="{{ old('stock_venta') }}">
                <input type="hidden" name="stock_minimo" value="{{ old('stock_minimo') }}">
                <input type="hidden" name="force_create" value="1">

                <button class="rounded-xl bg-slate-900 text-white px-4 py-2 hover:bg-slate-800">
                    Sí, crear igual
                </button>

                <a href="{{ route('productos.create') }}" class="ml-2 rounded-xl border px-4 py-2 hover:bg-slate-50">
                    No, volver a editar
                </a>
            </form>
        </div>
    @endif

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

    <form method="POST" action="{{ route('productos.store') }}">
        @include('productos._form')
    </form>

@endsection