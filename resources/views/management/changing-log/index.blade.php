@extends('management.layout.app')

@section('title')
    更新日志
@endsection

@section('content')
    <div class="container py-3">
        <div class="row">
            <div class="col-12 text-center mb-2">
                当前版本
            </div>

            <div class="col-12 text-center h2 text-warning">
                {{ $notes->versionLabel }}
            </div>

            <div class="col-12 text-center mb-2">
                更新日期：{{ $notes->updateDate }}
            </div>

            <div class="col-sm-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                <div class="accordion" id="version-list">
                    @foreach($notes->logs as $log)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading-{{ $loop->index }}">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#accordion-{{ $loop->index }}"
                                        aria-expanded="false" aria-controls="accordion-{{ $loop->index }}">
                                    <span style="font-size: large">{{ $log->groupName }}</span>
                                </button>
                            </h2>

                            <div id="accordion-{{ $loop->index }}" class="accordion-collapse collapse"
                                 aria-labelledby="heading-{{ $loop->index }}"
                                 data-bs-parent="#version-list">
                                <div class="accordion-body">
                                    @foreach($log->subGroups as $group)
                                        <div class="mb-3">
                                            <div class="h4">
                                                {{ $group->name }}（{{ $group->date }}）
                                            </div>

                                            @foreach($group->details as $detail)
                                                <div class="mb-2">
                                                    <div class="h6">{{ $detail->type }}</div>

                                                    <ul>
                                                        @foreach($detail->desc as $desc)
                                                            <li>{{ $desc }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function () {
            // Show the first child
            let selector = $('#version-list').children('.accordion-item').first()
            selector.find('button').removeClass('collapsed')
            selector.find('.collapse').addClass('show')
        });
    </script>
@endsection
