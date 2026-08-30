@extends('layouts.admin')

@section('title', 'Servicio Nuevo - fn peluqueria')
@section('h1', 'Servicio Nuevo')
@section('sub', 'Cargá nombre y precio del servicio.')

@section('content')
    <form method="POST" action="{{ route('servicios.store') }}">
        @include('servicios._form')
    </form>
@endsection