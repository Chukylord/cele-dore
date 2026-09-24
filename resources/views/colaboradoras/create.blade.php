@extends('layouts.admin')

@section('title', 'Colaboradora nueva - Cele Dore Estilista')
@section('h1', 'Colaboradora Nueva')
@section('sub', 'Cargá los datos de la colaboradora.')

@section('content')
    <form method="POST" action="{{ route('colaboradoras.store') }}" class="fn-form-card">
        @include('colaboradoras._form')
    </form>
@endsection
