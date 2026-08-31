@extends('layouts.admin')

@section('title', 'Nueva fichada - FN Peluquería')
@section('h1', 'Nueva Fichada')
@section('sub', 'Registrar horas trabajadas de una colaboradora.')

@section('content')
    <form method="POST" action="{{ route('fichadas.store') }}" class="fn-form-card">
        @include('fichadas._form')
    </form>
@endsection
