<template>
  <div>
    <div class="container">
      <div v-if="isWholesaleDiscountAllowed">
        <edit-item-wholesale-discount
          v-for="wholesaleDiscount in wholesaleDiscounts"
          v-bind:key="wholesaleDiscount.id"
          :wholesale_discount="wholesaleDiscount"
          :original_price="originalPrice"
          @onEdit="onEdit($event)"
          @onDelete="onDelete($event)"
        ></edit-item-wholesale-discount>
      </div>
      <div
        v-else
        class="d-flex align-items-center justify-content-center text-center"
      >
        <div class="py-5">
          <i
            class="icofont icofont-close-circled icofont-10x text-secondary"
            style="font-size: 120px"
          ></i>
          <p class="fs-5 text-secondary">此商品无法使用批发功能</p>
        </div>
      </div>
    </div>

    <!-- Add/Edit/Delete Modal -->
    <wholesale-discount-modal
      :item_id="itemId"
      :original_price="originalPrice"
      :wholesale_discount="selectedWholesaleDiscount"
      :action="action"
      @onSaveAdd="saveAdd($event)"
      @onSaveEdit="saveEdit($event)"
      @onConfirmDelete="confirmDelete($event)"
      @onResponse="(...args) => onResponse(...args)"
    ></wholesale-discount-modal>
  </div>
</template>

<script>
import WholesaleDiscountModal from "../../../shared/modals/WholesaleDiscountModal.vue";
import EditItemWholesaleDiscount from "./EditItemWholesaleDiscount.vue";
import { Modal } from "bootstrap";

const fakeWholesaleDiscounts = [
  {
    id: 1,
    rate: 0.2,
    min: 10,
    max: 19,
    step: 10,
  },
  {
    id: 2,
    rate: 0.3,
    min: 20,
    max: 29,
    step: 10,
  },
  {
    id: 3,
    rate: 0.4,
    min: 30,
    max: null,
    step: 10,
  },
];

export default {
  components: {
    EditItemWholesaleDiscount,
    WholesaleDiscountModal,
  },

  name: "edit-item-wholesale-discount-list",

  props: {
    item_id: Number,
    is_wholesale_discount_allowed: Boolean,
    original_price: Number,
    wholesale_discounts: Array,
  },

  data() {
    return {
      itemId: this.item_id,
      isWholesaleDiscountAllowed: this.is_wholesale_discount_allowed,
      originalPrice: this.original_price ?? null,
      // TODO remove mock data
      wholesaleDiscounts:
        !this.wholesale_discounts || this.wholesale_discounts?.length === 0
          ? this.getModifiedWholesaleDiscounts(fakeWholesaleDiscounts)
          : this.getModifiedWholesaleDiscounts(this.wholesale_discounts),

      action: null,
      selectedWholesaleDiscount: null,
    };
  },

  watch: {
    is_wholesale_discount_allowed: function (val) {
      this.isWholesaleDiscountAllowed = val;
    },
  },

  methods: {
    getModifiedWholesaleDiscounts(wholesaleDiscounts) {
      return wholesaleDiscounts.map((wholesaleDiscount, index) => {
        const isFirstElement = wholesaleDiscount === wholesaleDiscounts[0];
        const isLastElement =
          wholesaleDiscount ===
          wholesaleDiscounts[wholesaleDiscounts.length - 1];

        const previousMax = !isFirstElement
          ? wholesaleDiscounts[index - 1].max
          : null;
        const nextMin = !isLastElement
          ? wholesaleDiscounts[index + 1].min
          : null;

        return {
          ...wholesaleDiscount,
          previousMax,
          nextMin,
        };
      });
    },

    saveAdd(newWholesaleDiscount) {
      // TODO
    },

    saveEdit(wholesaleDiscount) {
      this.selectedWholesaleDiscount = wholesaleDiscount;
    },

    onAdd() {
      console.log("openAddModal()");

      this.action = {
        name: "添加",
        enName: "Add",
        value: "add",
        contentType: "form",
        button: {
          confirm: {
            name: "添加",
            enName: "Add",
            class: "btn-primary",
          },
          cancel: {
            name: "取消",
            enName: "Cancel",
            class: "btn-outline-danger",
          },
        },
      };
      this.selectedWholesaleDiscount = null;
      console.log(this.action, this.selectedWholesaleDiscount);
      this.openModal();
    },

    onEdit(wholesaleDiscount) {
      console.log("openEditModal()");

      this.action = {
        name: "编辑",
        enName: "Edit",
        value: "edit",
        contentType: "form",
        button: {
          confirm: {
            name: "保存",
            enName: "Save",
            class: "btn-primary",
          },
          cancel: {
            name: "取消",
            enName: "Cancel",
            class: "btn-outline-danger",
          },
        },
      };
      this.selectedWholesaleDiscount = wholesaleDiscount;
      console.log(this.action, this.selectedWholesaleDiscount);
      this.openModal();
    },

    onDelete(wholesaleDiscount) {
      console.log("openDeleteModal()");

      this.action = {
        name: "删除",
        enName: "Delete",
        value: "delete",
        contentType: "confirmation",
        button: {
          confirm: {
            name: "删除",
            enName: "Delete",
            class: "btn-danger",
          },
          cancel: {
            name: "取消",
            enName: "Cancel",
            class: "btn-outline-primary",
          },
        },
      };
      this.selectedWholesaleDiscount = wholesaleDiscount;
      console.log(this.action, this.selectedWholesaleDiscount);
      this.openModal();
    },

    openModal() {
      const wholesaleDiscountModal = new Modal(
        document.getElementById("wholesaleDiscountModal")
      );
      wholesaleDiscountModal.show();
    },

    confirmDelete(wholesaleDiscount) {
      this.selectedWholesaleDiscount = wholesaleDiscount;
    },

    onResponse(...args) {
      this.$emit("onResponse", ...args);
    },
  },
};
</script>