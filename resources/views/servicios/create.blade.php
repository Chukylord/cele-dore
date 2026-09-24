@extends('layouts.admin')

@section('title', 'Servicio nuevo - Cele Dore Estilista')
@section('h1', 'Servicio Nuevo')
@section('sub', 'Cargá nombre y precio del servicio.')

@section('content')
    <form method="POST" action="{{ route('servicios.store') }}" class="fn-form-card">
        @include('servicios._form')
    </form>
@endsection
