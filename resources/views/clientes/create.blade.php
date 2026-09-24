@extends('layouts.admin')

@section('title', 'Cliente nuevo - Cele Dore Estilista')
@section('h1', 'Cliente Nuevo')
@section('sub', 'Cargá los datos del cliente.')

@section('content')
    <form method="POST" action="{{ route('clientes.store') }}" class="fn-form-card">
        @include('clientes._form')
    </form>
@endsection
