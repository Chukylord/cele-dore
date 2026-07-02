@extends('layouts.admin')

@section('title', 'Editar Colaboradora - Cele Dore Estilista')
@section('h1', 'Editar Colaboradora')
@section('sub', 'Modificá los datos de la colaboradora.')

@section('content')
    <form method="POST" action="{{ route('colaboradoras.update', $colaboradora) }}">
        @method('PUT')
        @include('colaboradoras._form', ['colaboradora' => $colaboradora])
    </form>
@endsection