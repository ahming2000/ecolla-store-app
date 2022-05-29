<div class="modal fade" tabindex="-1" id="edit-element-modal" aria-hidden="true"
     data-element-type="null" data-element="null">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="h5 modal-title">
                    编辑
                </div>

                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="form-floating mb-2">
                    <input type="text" class="form-control" name="name" id="name-input-edit"
                           placeholder="名字（中文）" autofocus>

                    <label for="name-input-edit">
                        名字（中文）
                    </label>

                    <div class="invalid-feedback">
                        此为必填项目
                    </div>
                </div>

                <div class="form-floating">
                    <input type="text" class="form-control" name="name_en" id="name-en-input-edit"
                           placeholder="名字（英文）">

                    <label for="name-en-input-edit">
                        名字（英文）
                    </label>

                    <div class="invalid-feedback">
                        此为必填项目
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger" onclick="openDeleteElementModal(event)">
                    <i class="bi bi-trash"></i> 删除
                </button>

                <button type="button" class="btn btn-primary" onclick="updateItemSettingElement(event)">
                    <i class="bi bi-save"></i> 更新
                </button>
            </div>
        </div>
    </div>
</div>
