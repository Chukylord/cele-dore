@extends('layouts.admin')

@section('title', 'Editar cliente - FN Peluquería')
@section('h1', 'Editar Cliente')
@section('sub', 'Modificá los datos del cliente.')

@section('content')
    <form method="POST" action="{{ route('clientes.update', $cliente) }}" class="fn-form-card">
        @method('PUT')
        @include('clientes._form', ['cliente' => $cliente])
    </form>
@endsection
