@extends('layouts.admin')

@section('title', 'Cliente Nuevo - fn peluqueria')
@section('h1', 'Cliente Nuevo')
@section('sub', 'Cargá los datos del cliente.')

@section('content')
    <form method="POST" action="{{ route('clientes.store') }}">
        @include('clientes._form')
    </form>
@endsection