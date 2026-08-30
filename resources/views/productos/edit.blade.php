@extends('layouts.admin')

@section('title', 'Editar Producto - fn peluqueria')
@section('h1', 'Editar Producto')
@section('sub', 'Modificá los datos del producto.')

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

    @if(session('ok'))
        <div class="mb-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <form method="POST" action="{{ route('productos.update', $producto) }}">
        @method('PUT')
        @include('productos._form', ['producto' => $producto])
    </form>
@endsection