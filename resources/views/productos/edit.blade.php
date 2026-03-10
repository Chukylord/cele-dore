@extends('layouts.admin')

@section('title', 'Editar Producto - Peluquería TOP')
@section('h1', 'Editar Producto')
@section('sub', 'Modificá los datos del producto.')

@section('content')
    <form method="POST" action="{{ route('productos.update', $producto) }}">
        @method('PUT')
        @include('productos._form', ['producto' => $producto])
    </form>
@endsection