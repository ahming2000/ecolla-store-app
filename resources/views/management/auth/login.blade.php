@extends('management.layout.app')

@section('title')
    登录
@endsection

@section('content')
    <div class="container py-3">
        <div class="position-absolute top-50 start-50 translate-middle">
            <div class="card" style="width: 400px">
                <div class="card-header">登录</div>

                <div class="card-body">
                    <form action="{{ route('api.login') }}" method="post">
                        @csrf

                        <div class="form-floating mb-1">
                            <input type="text" class="form-control @error('username') is-invalid @enderror"
                                   name="username" id="username-input" value="{{ old('username') ?? "" }}"
                                   placeholder="账户ID" autofocus>

                            <label for="username-input">账户ID</label>

                            @error('username')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-floating mb-1">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" id="password-input" placeholder="密码">

                            <label for="password-input">密码</label>

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="remember"
                                   id="remember-input" @checked(old('remember'))>

                            <label class="form-check-label" for="remember-input">
                                保持登录
                            </label>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-primary">
                                <i class="bi bi-box-arrow-in-left"></i>
                                登录
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
