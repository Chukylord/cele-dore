@extends('layouts.admin')

@section('title', 'Editar compra - FN Peluquería')
@section('h1', 'Editar Compra')
@section('sub', 'Modificar compra y corregir stock automáticamente.')

@section('content')
    <form method="POST" action="{{ route('compras.update', $compra) }}" class="fn-form-card">
        @method('PUT')
        @include('compras._form', ['compra' => $compra])
    </form>
@endsection
