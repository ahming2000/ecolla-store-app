<style scoped>
li:hover {
  cursor: pointer;
}
</style>

<template>
  <div>
    <!-- Tab Panel -->
    <div class="">
      <ul
        class="nav nav-pills mb-3 pb-3 flex-nowrap overflow-auto row-cols-auto"
        id="pills-tab"
        role="tablist"
      >
        <li class="nav-item" role="presentation">
          <a
            class="nav-link active"
            id="pills-basic-info-tab"
            data-bs-toggle="pill"
            data-bs-target="#pills-basic-info"
            role="tab"
            aria-controls="pills-basic-info"
            aria-selected="true"
            >基本资讯</a
          >
        </li>
        <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            id="pills-images-tab"
            data-bs-toggle="pill"
            data-bs-target="#pills-images"
            role="tab"
            aria-controls="pills-images"
            aria-selected="false"
            >基本照片</a
          >
        </li>
        <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            id="pills-category-tab"
            data-bs-toggle="pill"
            data-bs-target="#pills-category"
            role="tab"
            aria-controls="pills-category"
            aria-selected="false"
            >商品类别/标签</a
          >
        </li>
        <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            id="pills-variation-tab"
            data-bs-toggle="pill"
            data-bs-target="#pills-variation"
            role="tab"
            aria-controls="pills-variation"
            aria-selected="false"
            >规格资讯</a
          >
        </li>
        <!-- <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            id="pills-wholesale-discount-tab"
            data-bs-toggle="pill"
            data-bs-target="#pills-wholesale-discount"
            role="tab"
            aria-controls="pills-wholesale-discount"
            aria-selected="false"
            >批发折扣管理</a
          >
        </li> -->
        <li class="nav-item" role="presentation">
          <a
            class="nav-link"
            id="pills-util-tab"
            data-bs-toggle="pill"
            data-bs-target="#pills-util"
            role="tab"
            aria-controls="pills-util"
            aria-selected="false"
            >其他商品设定</a
          >
        </li>
      </ul>
    </div>

    <!-- Content -->
    <div class="tab-content position-relative" id="pills-tabContent">
      <div
        class="tab-pane fade show active"
        id="pills-basic-info"
        role="tabpanel"
        aria-labelledby="pills-basic-info-tab"
      >
        <edit-item-basic-info
          :item_info="itemInfo"
          @onResponse="(...args) => onResponse(...args)"
        ></edit-item-basic-info>
      </div>
      <div
        class="tab-pane fade"
        id="pills-images"
        role="tabpanel"
        aria-labelledby="pills-images-tab"
      >
        <edit-item-image-list
          :item_id="item.id"
          :images="item.images"
          @onResponse="(...args) => onResponse(...args)"
        ></edit-item-image-list>
      </div>
      <div
        class="tab-pane fade"
        id="pills-category"
        role="tabpanel"
        aria-labelledby="pills-category-tab"
      >
        <edit-item-category
          :item_id="item.id"
          :allCategories="allCategories"
          :categories="item.categories"
          @onResponse="(...args) => onResponse(...args)"
        ></edit-item-category>
      </div>
      <div
        class="tab-pane fade"
        id="pills-variation"
        role="tabpanel"
        aria-labelledby="pills-variation-tab"
      >
        <edit-item-variation-list
          :item_id="item.id"
          :variations="variations"
          @onResponse="(...args) => onResponse(...args)"
          @onVariationsUpdated="onVariationsUpdated($event)"
        ></edit-item-variation-list>
      </div>
      <div
        class="tab-pane fade"
        id="pills-wholesale-discount"
        role="tabpanel"
        aria-labelledby="pills-wholesale-discount-tab"
      >
        <edit-item-wholesale-discount-list
          :item_id="item.id"
          :is_wholesale_discount_allowed="isWholesaleDiscountAllowed"
          :wholesale_discounts="item.discounts"
          :original_price="firstVariationPrice"
        ></edit-item-wholesale-discount-list>
      </div>
      <div
        class="tab-pane fade"
        id="pills-util"
        role="tabpanel"
        aria-labelledby="pills-util-tab"
      >
        <util-table :util="item.util" :itemId="item.id"></util-table>
      </div>
      <message-toast
        aria-live="polite"
        aria-atomic="true"
        class="position-fixed top-0 start-50 translate-middle-x min-vw-100"
        style="z-index: 2000"
        :message="messageData"
      ></message-toast>
    </div>
  </div>
</template>


<script>
import MessageToast from "../../shared/toasts/MessageToast.vue";
import EditItemBasicInfo from "./EditItemBasicInfo.vue";
import EditItemCategory from "./EditItemCategory.vue";
import EditItemImageList from "./images/EditItemImageList.vue";
import UtilTable from "./UtilTable.vue";
import EditItemVariationList from "./variations/EditItemVariationList.vue";
import EditItemWholesaleDiscountList from "./wholesales/EditItemWholesaleDiscountList.vue";
import { Toast } from "bootstrap";

export default {
  name: "edit-item",

  components: {
    EditItemBasicInfo,
    EditItemCategory,
    EditItemVariationList,
    EditItemWholesaleDiscountList,
    UtilTable,
    EditItemImageList,
    MessageToast,
  },

  props: {
    item: Object,
    allCategories: Array,
  },

  data() {
    return {
      // Extracted basic info from item
      itemInfo: {
        id: this.item.id ?? null,
        name: this.item.name ?? "",
        name_en: this.item.name_en ?? "",
        desc: this.item.desc ?? "",
        origin: this.item.origin ?? "",
        origin_en: this.item.origin_en ?? "",
        created_at: this.item.created_at ?? null,
        updated_at: this.item.updated_at ?? null,
      },
      variations: this.item.variations ?? [],
      isWholesaleDiscountAllowed: false,
      firstVariationPrice: this.item.variations[0]?.price ?? null,
      messageData: { message: "", type: "" },
    };
  },

  created() {
    this.checkWholesaleDiscountAllowed(this.variations);
    console.log(
      "this.isWholesaleDiscountAllowed",
      this.isWholesaleDiscountAllowed
    );
  },

  watch: {
    variations: function (val) {
      this.checkWholesaleDiscountAllowed(val);
      console.log(
        "this.isWholesaleDiscountAllowed",
        this.isWholesaleDiscountAllowed
      );
    },
  },

  methods: {
    onResponse(...args) {
      console.log(args);
      this.messageData = {
        message: args[0],
        type: args[1],
      };

      const option = {
        delay: 3000,
      };

      var toastElList = [].slice.call(document.querySelectorAll(".toast"));
      var toastList = toastElList.map(function (toastEl) {
        return new Toast(toastEl, option);
      });

      toastList[0].show();
    },

    onVariationsUpdated(variations) {
      this.variations = variations;
    },

    checkWholesaleDiscountAllowed(variations) {
      const variationPrices = variations.map((variation) => variation.price);
      const isPriceEqual = (currentPrice) =>
        currentPrice === variationPrices[0];
      this.isWholesaleDiscountAllowed = variationPrices.every(isPriceEqual);
    },
  },
};
</script>
