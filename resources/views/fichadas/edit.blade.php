@extends('layouts.admin')

@section('title', 'Editar Fichada - Cele Dore Estilista')
@section('h1', 'Editar Fichada')
@section('sub', 'Modificar registro de horas trabajadas.')

@section('content')
    <form method="POST" action="{{ route('fichadas.update', $fichada) }}">
        @method('PUT')
        @include('fichadas._form', ['fichada' => $fichada])
    </form>
@endsection