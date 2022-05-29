@extends('management.layout.app')

@section('title')
    网页设置
@endsection

@section('content')
    <div class="container py-3">
        <div class="row">
            <div class="col-sm-12 col-md-8 col-lg-6 offset-md-2 offset-lg-3">
                <div class="h1 mb-4">网页设置</div>

                <div class="element-parent mb-4" data-element-type="origin">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="h3 my-auto">出产地过滤</div>

                        <button type="button" class="btn btn-primary" onclick="openCreateElementModal(event)">
                            <i class="bi bi-plus-lg"></i> 添加出产地
                        </button>
                    </div>

                    @include('management.setting.element-container', ['elements' => $origins, 'type' => 'origin'])
                </div>

                <div class="element-parent mb-4" data-element-type="category">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="h3 my-auto">类别过滤</div>

                        <button type="button" class="btn btn-primary" onclick="openCreateElementModal(event)">
                            <i class="bi bi-plus-lg"></i> 添加类别
                        </button>
                    </div>

                    @include('management.setting.element-container', ['elements' => $categories, 'type' => 'category'])
                </div>

                @include('management.setting.create-element-modal')

                @include('management.setting.edit-element-modal')

                @include('management.setting.delete-element-modal')

                <div class="mb-4">
                    <div class="h3">邮费</div>

                    <div class="d-flex justify-content-between">
                        <div class="input-group">
                            <span class="input-group-text">RM</span>

                            <input type="number"
                                   class="form-control"
                                   id="shipping-fee-input"
                                   name="shipping_fee"
                                   value="{{ \App\Models\SystemConfig::getShippingFee() }}"
                                   placeholder="价格">

                            <button class="btn btn-primary" onclick="updateShippingFee(event)">
                                <i class="bi bi-save"></i> 保存
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="d-flex align-content-center">
                            <span class="h3 my-auto me-2">邮费折扣</span>

                            <div class="form-check form-switch my-auto">
                                <input class="form-check-input" type="checkbox" onchange="toggleShippingDiscount(event)"
                                       name="shipping_discount_is_activated" @checked(\App\Models\SystemConfig::shippingDiscountIsActivated())>
                            </div>
                        </div>

                        <button class="btn btn-primary" onclick="updateShippingDiscount(event)">
                            <i class="bi bi-save"></i> 保存
                        </button>
                    </div>

                    <div class="input-group mb-1">
                        <span class="input-group-text">超过 RM</span>

                        <input type="number"
                               class="form-control"
                               name="shipping_discount_threshold"
                               id="shipping-discount-threshold-input"
                               value="{{ \App\Models\SystemConfig::getShippingDiscountThreshold() }}"
                               placeholder="价格">

                        <span class="input-group-text">后即可包邮</span>
                    </div>

                    <div class="input-group">
                        <span class="input-group-text">备注</span>

                        <input type="text"
                               class="form-control"
                               name="shipping_discount_desc"
                               id="shipping-discount-desc-input"
                               value="{{ \App\Models\SystemConfig::getShippingDiscountDesc() }}"
                               placeholder="免运备注">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        const updateShippingFee = (event) => {
            let button = $(event.target)
            let fee = $('#shipping-fee-input').val()

            startLoading(button)

            axios.patch('/api/setting/shipping/fee', {
                shipping_fee: fee,
            }).then((res) => {
                if (res.data.isUpdated) {
                    addNotification('通知', '邮费已成功更新！')
                }

                stopLoading(button)
            }).catch((error) => {
                console.error(error)
                stopLoading(button)
            })
        }

        const toggleShippingDiscount = (event) => {
            let isChecked = $(event.target).attr('checked')
            let isActivated = !isChecked

            $(event.target).attr('checked', isActivated)

            axios.patch('/api/setting/shipping/discount', {
                shipping_discount_is_activated: isActivated
            }).then((res) => {
                if (res.data.isUpdated) {
                    if (isActivated) {
                        addNotification('通知', '成功开启免邮功能！')
                    } else {
                        addNotification('通知', '成功关闭免邮功能！')
                    }
                }
            }).catch((error) => {
                console.error(error)
            })
        }

        const updateShippingDiscount = (event) => {
            let threshold = $('#shipping-discount-threshold-input').val()
            let desc = $('#shipping-discount-desc-input').val()
            let button = $(event.target)

            startLoading(button)

            axios.patch('/api/setting/shipping/discount', {
                shipping_discount_threshold: threshold,
                shipping_discount_desc: desc,
            }).then((res) => {
                if (res.data.isUpdated) {
                    addNotification('通知', '成功保存免邮设定！')
                }

                stopLoading(button)
            }).catch((error) => {
                console.error(error)
                stopLoading(button)
            })
        }
    </script>
@endsection
