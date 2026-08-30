@extends('layouts.admin')

@section('title', 'Colaboradora Nueva - fn peluqueria')
@section('h1', 'Colaboradora Nueva')
@section('sub', 'Cargá los datos de la colaboradora.')

@section('content')
    <form method="POST" action="{{ route('colaboradoras.store') }}">
        @include('colaboradoras._form')
    </form>
@endsection