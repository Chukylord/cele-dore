@extends('layouts.admin')

@section('title', 'Editar Proveedor - Peluquería TOP')
@section('h1', 'Editar Proveedor')
@section('sub', 'Modificá el nombre del proveedor.')

@section('content')
    <form method="POST" action="{{ route('proveedores.update', $proveedor) }}">
        @method('PUT')
        @include('proveedores._form', ['proveedor' => $proveedor])
    </form>
@endsection