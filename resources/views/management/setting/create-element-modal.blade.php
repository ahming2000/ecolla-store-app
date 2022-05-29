<div class="modal fade" id="create-element-modal" tabindex="-1" aria-hidden="true"
     data-element-type="null">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="h5 modal-title">
                    创建
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" name="name" id="name-input-create"
                           placeholder="名称（中文）" autofocus>

                    <label for="name-input-create">
                        名称（中文）
                    </label>

                    <div class="invalid-feedback">
                        此为必填项目
                    </div>
                </div>

                <div class="form-floating">
                    <input type="text" class="form-control" name="name_en" id="name-en-input-create"
                           placeholder="名称（英文）">

                    <label for="name-en-input-create">
                        名称（英文）
                    </label>

                    <div class="invalid-feedback">
                        此为必填项目
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="createItemSettingElement(event)">
                    <i class="bi bi-save"></i> 创建
                </button>
            </div>
        </div>
    </div>
</div>
