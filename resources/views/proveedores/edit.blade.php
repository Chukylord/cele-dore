@extends('layouts.admin')

@section('title', 'Editar proveedor - Cele Dore Estilista')
@section('h1', 'Editar Proveedor')
@section('sub', 'Modificá el nombre del proveedor.')

@section('content')
    <form method="POST" action="{{ route('proveedores.update', $proveedor) }}" class="fn-form-card">
        @method('PUT')
        @include('proveedores._form', ['proveedor' => $proveedor])
    </form>
@endsection
