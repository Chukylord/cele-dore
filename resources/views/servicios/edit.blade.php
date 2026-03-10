@extends('layouts.admin')

@section('title', 'Editar Servicio - Peluquería TOP')
@section('h1', 'Editar Servicio')
@section('sub', 'Modificá el nombre o el precio del servicio.')

@section('content')
    <form method="POST" action="{{ route('servicios.update', $servicio) }}">
        @method('PUT')
        @include('servicios._form', ['servicio' => $servicio])
    </form>
@endsection