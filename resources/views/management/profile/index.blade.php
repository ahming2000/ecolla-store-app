@extends('management.layout.app')

@section('title')
    个人资料
@endsection

@section('content')
    <div class="container py-3">
        <div class="row">
            <div class="col-sm-12 col-md-8 col-lg-6 offset-md-2 offset-lg-3">
                <div class="h1">个人资料</div>

                <div class="mb-3">
                    <div class="h3">账户ID</div>

                    <div>
                        您的账户ID为
                        <span class="fw-bolder">
                            {{ Auth::user()->username }}
                        </span>
                    </div>

                    <small class="text-muted">
                        **账户ID需要联系网页管理员以进行修改
                    </small>
                </div>

                <form action="{{ route('management.profile.update') }}" method="post" class="mb-3">
                    @method('patch')
                    @csrf

                    <div class="d-flex justify-content-between mb-2">
                        <div class="h3 my-auto">更换密码</div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i>
                            更改
                        </button>
                    </div>

                    <div class="form-floating mb-2">
                        <input type="password" class="form-control @error('old_password') is-invalid @enderror"
                               name="old_password" id="old-password-input" placeholder="旧密码">

                        <label for="old-password-input">
                            旧密码
                        </label>

                        @error('old_password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-floating mb-2">
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               name="password" id="password-input" placeholder="新密码">

                        <label for="password-input">
                            新密码
                        </label>

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-floating mb-2">
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                               name="password_confirmation" id="password-confirmation-input" placeholder="密码确认">

                        <label for="password-confirmation-input">
                            密码确认
                        </label>

                        @error('password_confirmation')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
