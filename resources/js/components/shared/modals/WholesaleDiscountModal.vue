<template>
  <div>
    <div
      class="modal fade"
      id="wholesaleDiscountModal"
      tabindex="-1"
      aria-labelledby="wholesaleDiscountLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="wholesaleDiscountLabel">
              {{ actionName }}批发折扣
            </h5>
            <button
              type="button"
              class="btn-close"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body bg-light">
            <!-- form content -->
            <div v-if="actionContentType === 'form'" class="container">
              <div class="row mb-3">
                <div class="col-6">
                  <div class="form-group w-100">
                    <label class="label" for="wholesaleDiscountMin"
                      >批发的开始数量</label
                    >
                    <input
                      type="number"
                      class="form-control text-end"
                      id="wholesaleDiscountMin"
                      :min="
                        wholesaleDiscountPreviousMax
                          ? wholesaleDiscountPreviousMax + 1
                          : 0
                      "
                      :max="wholesaleDiscountMax - 1"
                      step="1"
                      :value="wholesaleDiscountMin"
                      @change="onChange($event, 'min')"
                    />
                  </div>
                </div>
                <div class="col-6">
                  <div class="form-group w-100">
                    <label class="label" for="wholesaleDiscountMax"
                      >批发的结束数量</label
                    >
                    <input
                      type="number"
                      class="form-control text-end"
                      id="wholesaleDiscountMax"
                      :min="wholesaleDiscountMin + 1"
                      :max="
                        wholesaleDiscountNextMin
                          ? wholesaleDiscountNextMin - 1
                          : null
                      "
                      step="1"
                      :value="wholesaleDiscountMax"
                      @change="onChange($event, 'max')"
                    />
                  </div>
                </div>
              </div>
              <div class="row mb-3">
                <p class="text-primary m-0">原价</p>
                <p class="text-primary fw-bold m-0">RM {{ originalPrice.toFixed(2) }}</p>
              </div>
              <div class="row mb-3">
                <edit-discount
                  :original_price="originalPrice"
                  :rate="wholesaleDiscountRate"
                  @onRateChange="onRateChange($event)"
                ></edit-discount>
              </div>
            </div>

            <!-- confirmation content -->
            <div
              v-else-if="actionContentType === 'confirmation'"
              class="container"
            >
              <div class="col">
                <p>确定删除此批发折扣？此动作无法挽回。</p>
              </div>
            </div>
          </div>
          <div class="modal-footer justify-content-center w-100">
            <div class="row w-100">
              <div class="col-6">
                <button
                  type="button"
                  :class="`btn ${actionButtonCancelClass} btn-md shadow-none w-100`"
                  data-bs-dismiss="modal"
                >
                  {{ actionButtonCancelName }}
                </button>
              </div>
              <div class="col-6">
                <button
                  type="submit"
                  :class="`btn ${actionButtonConfirmClass} btn-md w-100`"
                  data-bs-dismiss="modal"
                  :disabled="!isAllValid()"
                  @click.prevent="onPrimaryPressed()"
                >
                  {{ actionButtonConfirmName }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import EditDiscount from "../components/EditDiscount.vue";

export default {
  components: { EditDiscount },

  name: "wholesale-discount-modal",

  props: {
    item_id: Number,
    action: Object,
    original_price: Number,
    wholesale_discount: Object,
  },

  data() {
    return {
      actionName: this.action?.name ?? null,
      actionContentType: this.action?.contentType ?? null,
      actionButtonConfirmName: this.action?.button.confirm.name ?? null,
      actionButtonConfirmClass: this.action?.button.confirm.class ?? null,
      actionButtonCancelName: this.action?.button.cancel.name ?? null,
      actionButtonCancelClass: this.action?.button.cancel.class ?? null,

      originalPrice: this.original_price ?? null,

      wholesaleDiscountId: this.wholesale_discount?.id ?? null,
      wholesaleDiscountRate: this.wholesale_discount?.rate ?? 0,
      wholesaleDiscountMin: this.wholesale_discount?.min ?? null,
      wholesaleDiscountMax: this.wholesale_discount?.max ?? null,
      wholesaleDiscountStep: this.wholesale_discount?.step ?? 0,
      wholesaleDiscountPreviousMax:
        this.wholesale_discount?.previousMax ?? null,
      wholesaleDiscountNextMin: this.wholesale_discount?.nextMin ?? null,
    };
  },

  watch: {
    wholesale_discount: function (val) {
      this.clearWholesaleDiscountData();
      this.fetchWholesaleDiscountData(val);
    },
    action: function (val) {
      this.clearActionData();
      this.fetchActionData(val);
    },
  },

  methods: {
    onPrimaryPressed() {
      console.log("onPrimaryPressed()");

      const wholesaleDiscountData = {};

      switch (this.action.value) {
        case "add": {
          this.$emit("onSaveAdd", wholesaleDiscountData);
          break;
        }
        case "edit": {
          this.$emit("onSaveEdit", wholesaleDiscountData);
          break;
        }
        case "delete": {
          this.$emit("onConfirmDelete", wholesaleDiscountData);
          break;
        }
      }
    },

    onChange(event, name) {
      let newValue = event.target.value.trim();
      let limitedValue;

      switch (name) {
        case "min": {
          limitedValue = this.minLimit(Number(newValue));
          this.wholesaleDiscountMin = limitedValue;
          event.target.value = limitedValue;
          break;
        }
        case "max": {
          limitedValue = this.maxLimit(Number(newValue));
          this.wholesaleDiscountMax = limitedValue;
          event.target.value = limitedValue;
          break;
        }
        default: {
        }
      }
    },

    onRateChange(newRate) {
      this.variationDiscountRate = newRate;
    },

    minLimit(value) {
      return value;
    },

    maxLimit(value) {
      return value;
    },

    fetchWholesaleDiscountData(val) {
      this.wholesaleDiscountId = val?.id ?? null;
      this.wholesaleDiscountRate = val?.rate ?? 0;
      this.wholesaleDiscountMin = val?.min ?? null;
      this.wholesaleDiscountMax = val?.max ?? null;
      this.wholesaleDiscountStep = val?.step ?? 0;
    },

    fetchActionData(val) {
      this.actionName = val?.name ?? null;
      this.actionContentType = val?.contentType ?? null;
      this.actionButtonConfirmName = val?.button.confirm.name ?? null;
      this.actionButtonConfirmClass = val?.button.confirm.class ?? null;
      this.actionButtonCancelName = val?.button.cancel.name ?? null;
      this.actionButtonCancelClass = val?.button.cancel.class ?? null;
      this.wholesaleDiscountPreviousMax =
        this.wholesale_discount?.previousMax ?? null;
      this.wholesaleDiscountNextMin = this.wholesale_discount?.nextMin ?? null;
    },

    clearWholesaleDiscountData() {
      this.wholesaleDiscountId = null;
      this.wholesaleDiscountRate = 0;
      this.wholesaleDiscountMin = null;
      this.wholesaleDiscountMax = null;
      this.wholesaleDiscountStep = 0;
      this.wholesaleDiscountPreviousMax = null;
      this.wholesaleDiscountNextMin = null;
    },

    clearActionData() {
      this.actionName = null;
      this.actionContentType = null;
      this.actionButtonConfirmName = null;
      this.actionButtonConfirmClass = null;
      this.actionButtonCancelName = null;
      this.actionButtonCancelClass = null;
    },

    isAllValid() {
      return (
        this.variationName &&
        this.variationEnName &&
        this.variationBarcode &&
        this.variationBarcodeError === ""
      );
    },

    onResponse(...args) {
      this.$emit("onResponse", ...args);
    },
  },
};
</script>