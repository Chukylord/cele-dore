@extends('layouts.admin')

@section('title', 'Proveedor nuevo - Cele Dore Estilista')
@section('h1', 'Proveedor Nuevo')
@section('sub', 'Cargá el nombre del proveedor.')

@section('content')
    <form method="POST" action="{{ route('proveedores.store') }}" class="fn-form-card">
        @include('proveedores._form')
    </form>
@endsection
