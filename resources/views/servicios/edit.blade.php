@extends('layouts.admin')

@section('title', 'Editar servicio - FN Peluquería')
@section('h1', 'Editar Servicio')
@section('sub', 'Modificá el nombre o el precio del servicio.')

@section('content')
    <form method="POST" action="{{ route('servicios.update', $servicio) }}" class="fn-form-card">
        @method('PUT')
        @include('servicios._form', ['servicio' => $servicio])
    </form>
@endsection
