@extends('layouts.admin')

@section('title', 'Editar Compra - Cele Dore Estilista')
@section('h1', 'Editar Compra')
@section('sub', 'Modificar compra y corregir stock automáticamente.')

@section('content')
    <form method="POST" action="{{ route('compras.update', $compra) }}">
        @method('PUT')
        @include('compras._form', ['compra' => $compra])
    </form>
@endsection