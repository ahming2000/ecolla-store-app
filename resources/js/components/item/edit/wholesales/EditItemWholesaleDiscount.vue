<template>
  <li class="card my-2">
    <div class="card-body">
      <div class="row g-3 d-flex align-items-center">
        <div class="col-12 col-sm-12 col-md-6">
          <div class="row">
            <div class="col-6">
              <div class="row flex-nowrap">
                <div class="col-2 col-md-3"></div>
                <div class="col-2 col-md-3 text-nowrap">
                  {{ wholesaleDiscountMin }}
                </div>
                <div class="col-2 col-md-3">
                  <span><i class="icofont icofont-caret-right"></i></span>
                </div>
                <div
                  v-if="wholesaleDiscountMax"
                  class="col-2 col-md-3 text-nowrap"
                >
                  {{ wholesaleDiscountMax }}
                </div>
                <div v-else class="col-2 col-md-3 text-nowrap">
                  <i class="icofont icofont-infinite"></i>
                </div>
              </div>
            </div>
            <div class="col-6 d-flex align-items-center justify-content-start">
              <!-- Original Price -->
              <span
                :class="`badge rounded-pill ${originalPriceClass} shadow-none p-2 me-2`"
              >
                RM {{ originalPrice.toFixed(2) }}
              </span>
              <!-- Discounted Price -->
              <span
                v-if="wholesaleDiscountRate !== null"
                class="badge rounded-pill bg-success shadow-none p-2"
              >
                RM {{ wholesaleDiscountedPrice.toFixed(2) }}
              </span>
            </div>
          </div>
        </div>
        <div class="col-12 col-sm-12 col-md-6">
          <div class="row justify-content-center">
            <div class="col-6 col-sm-5 col-md-4 col-lg-3">
              <button
                class="btn btn-primary w-100"
                type="submit"
                @click.prevent="onEdit()"
              >
                编辑
              </button>
            </div>
            <div class="col-6 col-sm-5 col-md-4 col-lg-3">
              <button
                class="btn btn-danger w-100"
                type="submit"
                @click.prevent="onDelete()"
              >
                删除
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </li>
</template>

<script>
export default {
  name: "edit-item-wholesale-discount",

  props: {
    original_price: Number,
    wholesale_discount: Object,
  },

  data() {
    return {
      originalPrice: this.original_price ?? null,
      wholesaleDiscountedPrice:
        (1 - this.wholesale_discount?.rate) * this.original_price,

      wholesaleDiscountId: this.wholesale_discount?.id ?? null,
      wholesaleDiscountRate: this.wholesale_discount?.rate ?? 0,
      wholesaleDiscountMin: this.wholesale_discount?.min ?? null,
      wholesaleDiscountMax: this.wholesale_discount?.max ?? null,
      wholesaleDiscountStep: this.wholesale_discount?.step ?? 0,
      wholesaleDiscountPreviousMax: this.wholesale_discount?.previousMax ?? null,
      wholesaleDiscountNextMin: this.wholesale_discount?.nextMin ?? null,
    };
  },

  watch: {
    wholesale_discount: function (val) {
      this.wholesaleDiscountedPrice =
        (1 - this.wholesale_discount?.rate) * this.original_price;
    },
  },

  computed: {
    originalPriceClass: {
      get: function () {
        return this.wholesaleDiscountRate !== null
          ? "bg-warning text-decoration-line-through"
          : "bg-success";
      },
    },
  },

  methods: {
    onEdit() {
      this.$emit("onEdit", this.wholesale_discount);
    },
    onDelete() {
      this.$emit("onDelete", this.wholesale_discount);
    },
  },
};
</script>