@extends('layouts.admin')

@section('title', 'Editar fichada - FN Peluquería')
@section('h1', 'Editar Fichada')
@section('sub', 'Modificar registro de horas trabajadas.')

@section('content')
    <form method="POST" action="{{ route('fichadas.update', $fichada) }}" class="fn-form-card">
        @method('PUT')
        @include('fichadas._form', ['fichada' => $fichada])
    </form>
@endsection
