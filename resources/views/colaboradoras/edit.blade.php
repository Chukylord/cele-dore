@extends('layouts.admin')

@section('title', 'Editar colaboradora - Cele Dore Estilista')
@section('h1', 'Editar Colaboradora')
@section('sub', 'Modificá los datos de la colaboradora.')

@section('content')
    <form method="POST" action="{{ route('colaboradoras.update', $colaboradora) }}" class="fn-form-card">
        @method('PUT')
        @include('colaboradoras._form', ['colaboradora' => $colaboradora])
    </form>
@endsection
