<div class="card shadow mb-3">
    <div class="card-body">
        <div class="h4 mb-3 fw-bold">
            @if(session('lang') == 'en')
                Customer Services
            @else
                备注
            @endif
        </div>

        <p>
            @if(session('lang') == 'en')
                If you have any problems, please contact us!
            @else
                如有任何问题，请联系我们！
            @endif
        </p>

        <p class="text-muted">
            <i class="bi bi-facebook"></i>
            <u>
                <a href="https://www.facebook.com/Ecolla-e%E5%8F%A3%E4%B9%90-2347940035424278" target="_blank">
                    @if(session('lang') == 'en')
                        Ecolla Official Facebook
                    @else
                        e口乐官方脸书
                    @endif
                </a>
            </u>
        </p>

        <p class="text-muted">
            <i class="bi bi-whatsapp"></i>
            <u>
                @if(session('lang') == 'en')
                    <a href="https://wa.link/2e1z4h" target="_blank">
                        WhatsApp Customer Services
                    </a>
                @else
                    <a href="https://wa.link/fcfum1" target="_blank">
                        WhatsApp 客服
                    </a>
                @endif
            </u>
        </p>

        <p class="text-muted">
            <i class="bi bi-phone"></i>
            <u>
                <a href="#" onclick="copyPhoneNumber(event)">
                    @if(session('lang') == 'en')
                        012-9862365 (Click to copy)
                    @else
                        012-9862365（点击进行复制）
                    @endif
                </a>
            </u>
        </p>
    </div>
</div>

<script>
    const copyPhoneNumber = (event) => {
        event.preventDefault();
        navigator.clipboard.writeText('+60129862365');
        @if(session('lang') == 'en')
            alert('Copy phone number successfully!');
        @else
            alert('已成功复制电话号码！');
        @endif
    }
</script>
