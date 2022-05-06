@extends('common.layout.app')

@section('menubar')
    @include('listing.en.layout.menubar')
@endsection

@section('header')
    @include('listing.en.layout.header')
@endsection

@section('footer')
    @include('listing.en.layout.footer')
@endsection

@section('utility')
    @include('listing.ch.layout.speed-dial')
    @include('common.layout.notification')
@endsection
