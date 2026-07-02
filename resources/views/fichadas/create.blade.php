@extends('layouts.admin')

@section('title', 'Nueva Fichada - Cele Dore Estilista')
@section('h1', 'Nueva Fichada')
@section('sub', 'Registrar horas trabajadas de una colaboradora.')

@section('content')
    <form method="POST" action="{{ route('fichadas.store') }}">
        @include('fichadas._form')
    </form>
@endsection