<div class="modal fade" tabindex="-1" id="delete-element-modal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="d-flex justify-content-center align-content-center m-5">
                    确定要删除 “<span class="element-name"></span>” 吗？
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">
                    取消
                </button>

                <button type="button" class="btn btn-danger" onclick="deleteItemSettingElement(event)">
                    <i class="bi bi-trash"></i> 确定
                </button>
            </div>
        </div>
    </div>
</div>
