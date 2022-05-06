@extends('common.layout.app')

@section('menubar')
    @include('listing.ch.layout.menubar')
@endsection

@section('header')
    @include('listing.ch.layout.header')
@endsection

@section('footer')
    @include('listing.ch.layout.footer')
@endsection

@section('utility')
    @include('listing.ch.layout.speed-dial')
    @include('common.layout.notification')
@endsection
