@extends('common.app')

@section('menubar')
    @include('listing.layout.menubar')
@endsection

@section('footer')
    @include('listing.layout.footer')
@endsection

@section('utility')
    @include('listing.layout.speed-dial')
    @include('common.notification')
@endsection
