@extends('layouts.admin')

@section('title', 'Nuevo Gasto - Cele Dore Estilista')
@section('h1', 'Nuevo Gasto')
@section('sub', 'Registrar egreso manual.')

@section('content')
    <form method="POST" action="{{ route('gastos.store') }}">
        @include('gastos._form')
    </form>
@endsection