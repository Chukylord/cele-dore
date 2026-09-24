@extends('layouts.admin')

@section('title', 'Nuevo gasto - Cele Dore Estilista')
@section('h1', 'Nuevo Gasto')
@section('sub', 'Registrar egreso manual.')

@section('content')
    <form method="POST" action="{{ route('gastos.store') }}" class="fn-form-card">
        @include('gastos._form')
    </form>
@endsection
