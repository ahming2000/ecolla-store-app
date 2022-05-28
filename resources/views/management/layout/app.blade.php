@extends('common.app')

@section('menubar')
    @include('management.layout.menubar')
@endsection

@section('utility')
    @include('common.notification')

    @if(session('notification'))
        <script>
            $(document).ready(() => {
                @if(is_array(session('notification')))
                    @foreach(session('notification') as $message)
                        addNotification('通知', '{{ $message }}')
                    @endforeach
                @else
                    addNotification('通知', '{{ session('notification') }}')
                @endif
            })
        </script>
    @endif
@endsection
