@extends('layouts.admin')

@section('title', 'Proveedor Nuevo - fn peluqueria')
@section('h1', 'Proveedor Nuevo')
@section('sub', 'Cargá el nombre del proveedor.')

@section('content')
    <form method="POST" action="{{ route('proveedores.store') }}">
        @include('proveedores._form')
    </form>
@endsection