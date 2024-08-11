<template>
  <div class="container">
    <div class="form-group w-100">
      <label class="label" for="discountRateRange">折扣率</label>
      <input
        type="range"
        class="form-range"
        max="1"
        min="0"
        step="0.0001"
        v-model="discountRateRange"
        id="discountRateRange"
      />
    </div>
    <div class="row mb-3">
      <div class="form-group w-100">
        <div class="input-group">
          <span class="input-group-text" id="negativeCurrencyUnit">- RM</span>
          <input
            type="number"
            class="form-control"
            id="discountPrice"
            :value="discountPrice.toFixed(2)"
            @change="onChange($event, 'discountPrice')"
          />
          <div class="invalid-feedback">必须 RM0.00 或以上</div>
          <span class="input-group-text" id="openBracket">(</span>
          <input
            type="number"
            class="form-control text-end"
            id="discountRatePercentage"
            min="0"
            max="100"
            step="1"
            :value="discountRatePercentage.toFixed(2)"
            @change="onChange($event, 'discountRatePercentage')"
          />
          <span class="input-group-text" id="percentage">%)</span>
        </div>
      </div>
    </div>

    <div class="form-group mb-3 w-100 ">
      <label class="label text-primary" for="discountedPrice">折扣后的价钱</label>
      <div class="input-group">
        <span class="input-group-text text-primary fw-bold" id="currencyUnit">RM</span>
        <input
          type="number"
          class="form-control text-primary fw-bold"
          id="discountedPrice"
          :value="discountedPrice.toFixed(2)"
          @change="onChange($event, 'discountedPrice')"
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "edit-discount",

  props: {
    original_price: Number,
    rate: Number,
  },

  data() {
    return {
      discountRate: this.rate ?? 0,
      originalPrice: this.original_price ?? 0,

      discountPrice: !this.rate ? 0 : this.original_price * this.rate,
      discountRatePercentage: !this.rate ? 0 : this.rate * 100,
      discountedPrice: this.original_price - this.original_price * this.rate,
    };
  },

  watch: {
    original_price: function (val) {
      this.fetchDiscountData(this.discountRate, val);
    },
    rate: function (val) {
      this.fetchDiscountData(val, this.originalPrice);
    },
    discountRate: function (val) {
      this.fetchDiscountData(val, this.originalPrice);
      this.$emit("onRateChange", val);
    },
  },

  computed: {
    discountRateRange: {
      get: function () {
        return !this.discountRate ? 0 : Number(this.discountRate);
      },
      set: function (val) {
        this.discountRate = Number(val);
      },
    },
  },

  methods: {
    clearDiscountData() {
      this.discountRate = 0;
      this.originalPrice = 0;

      this.discountPrice = 0;
      this.discountRatePercentage = 0;
      this.discountedPrice = this.original_price;
    },

    fetchDiscountData(rate, originalPrice) {
      this.discountRate = rate;
      this.originalPrice = originalPrice ?? 0;

      this.discountPrice = !rate ? 0 : originalPrice * rate;
      this.discountRatePercentage = !rate ? 0 : rate * 100;
      this.discountedPrice = originalPrice - this.discountPrice;
    },

    onChange(event, name) {
      let newValue = event.target.value.trim();
      let limitedValue;
      switch (name) {
        case "discountPrice": {
          limitedValue = this.discountedLimit(Number(newValue));
          this.discountPrice = limitedValue;
          this.discountRate = limitedValue / this.originalPrice;
          break;
        }
        case "discountRatePercentage": {
          limitedValue = this.percentageLimit(Number(newValue));
          this.discountRatePercentage = limitedValue;
          this.discountRate = limitedValue / 100;
          break;
        }
        case "discountedPrice": {
          limitedValue = this.discountedLimit(Number(newValue));
          this.discountedPrice = limitedValue;
          this.discountRate =
            (this.originalPrice - limitedValue) / this.originalPrice;
          break;
        }
        default: {
        }
      }
      event.target.value = limitedValue.toFixed(2);
    },

    percentageLimit(value) {
      if (value < 0) {
        return 0;
      } else if (value > 100) {
        return 100;
      } else {
        return value;
      }
    },

    discountLimit(value) {
      if (value < 0) {
        return 0;
      } else {
        return value;
      }
    },

    discountedLimit(value) {
      if (value < 0) {
        return 0;
      } else if (value > this.originalPrice) {
        return this.originalPrice;
      } else {
        return value;
      }
    },
  },
};
</script>