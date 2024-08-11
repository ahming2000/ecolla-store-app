<style lang="scss" scoped>
.add-variation {
  background-color: rgba(255, 255, 255, 0.5);
  width: 100%;
  padding-top: 50%; /* 2:1 Aspect Ratio */
  position: relative;
  border-radius: 15px;
  // box-shadow: 0 0 10px -2px hsla(0, 0%, 0%, 0.5);
  transition: all 0.3s ease;
  cursor: pointer;

  i {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    color: hsla(172, 100%, 47%, 0.5);
    font-size: 50px;
    transition: all 0.3s ease;
  }

  &:hover,
  &:active {
    background-color: #00eece;
    transition: all 0.3s ease;

    i {
      color: white;
      transition: all 0.3s ease;
    }
  }
}
</style>

<template>
  <div>
    <div class="container">
      <edit-item-variation
        v-for="variation in variationList"
        v-bind:key="variation.id"
        :variation="variation"
        @onEdit="openEditModal($event)"
        @onDelete="openDeleteModal($event)"
      ></edit-item-variation>

      <div
        class="add-variation"
        @click.prevent="openAddModal()"
        data-bs-toggle="modal"
        data-bs-target="#itemVariationModal"
      >
        <i class="icofont icofont-ui-add"></i>
      </div>
    </div>

    <!-- Add/Edit/Delete Modal -->
    <item-variation-modal
      :item_id="itemId"
      :variation="selectedVariation"
      :action="action"
      @onSaveAdd="saveAdd($event)"
      @onSaveEdit="saveEdit($event)"
      @onConfirmDelete="confirmDelete($event)"
      @onResponse="(...args) => onResponse(...args)"
    ></item-variation-modal>
  </div>
</template>

<script>
import ItemVariationModal from "../../../shared/modals/ItemVariationModal.vue";
import UploadImageModal from "../../../shared/modals/UploadImageModal.vue";
import EditItemVariation from "./EditItemVariation.vue";

export default {
  name: "edit-item-variation-list",

  components: { EditItemVariation, ItemVariationModal, UploadImageModal },

  props: {
    item_id: Number,
    variations: Array,
  },

  data() {
    return {
      itemId: this.item_id ?? null,
      variationList: this.variations ?? [],
      action: null,
      selectedVariation: null,
    };
  },

  methods: {
    openAddModal() {
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

      this.selectedVariation = null;

      console.log(this.action);
    },

    openEditModal(variation) {
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

      this.selectedVariation = variation;

      console.log(this.action, this.selectedVariation);
    },

    openDeleteModal(variation) {
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

      this.selectedVariation = variation;

      console.log(this.action, this.selectedVariation);
    },

    saveAdd(newVariation) {
      console.log("saveAdd()");

      console.log(newVariation);

      const body = {
        action: "add",
        variation: newVariation,
      };
      console.log(body);
      axios
        .patch(`/item/${this.itemId}/variation`, body)
        .then((res) => {
          console.log(res);
          if (res.data.ok) {
            this.$emit("onResponse", res.data.messages, "success");
            const addedVariation = {
              id: res.data.data.id,
              name: res.data.data.name,
              name_en: res.data.data.name_en,
              barcode: res.data.data.barcode,
              price: res.data.data.price,
              stock: res.data.data.stock,
              weight: res.data.data.weight,
              image: res.data.data.image,
              discount: newVariation.discount,
            };
            this.variationList = [...this.variationList, addedVariation];
            this.selectedVariation = null;

            this.$emit("onVariationsUpdated", this.variationList);
          } else {
            this.$emit("onResponse", res.data.messages, "error");
          }
        })
        .catch((error) => {
          console.error(error);
          this.$emit("onResponse", error.message, "error");
        });
    },

    saveEdit(variation) {
      console.log("saveEdit()");
      this.selectedVariation = variation;

      const body = {
        action: "update",
        variation: this.selectedVariation,
      };
      console.log(body);
      axios
        .patch(`/item/${this.itemId}/variation`, body)
        .then((res) => {
          console.log(res);
          if (res.data.ok) {
            this.$emit("onResponse", res.data.messages, "success");
            const editedVariation = {
              id: this.selectedVariation.info.id,
              name: this.selectedVariation.info.name,
              name_en: this.selectedVariation.info.name_en,
              barcode: this.selectedVariation.info.barcode,
              price: this.selectedVariation.info.price,
              stock: this.selectedVariation.info.stock,
              weight: this.selectedVariation.info.weight,
              image: this.selectedVariation.info.image,
              discount: this.selectedVariation.discount,
            };
            this.variationList = this.variationList.map((variation) => {
              if (variation.id === editedVariation.id) {
                return editedVariation;
              } else {
                return variation;
              }
            });
            this.selectedVariation = null;

            this.$emit("onVariationsUpdated", this.variationList);
          } else {
            this.$emit("onResponse", res.data.messages, "error");
          }
        })
        .catch((error) => {
          console.error(error);
          this.$emit("onResponse", error.message, "error");
        });
    },

    confirmDelete(variation) {
      console.log("confirmDelete()");
      this.selectedVariation = variation;

      const body = {
        action: "delete",
        variation: this.selectedVariation,
      };
      console.log(body);
      axios
        .patch(`/item/${this.itemId}/variation`, body)
        .then((res) => {
          console.log(res);
          if (res.data.ok) {
            this.$emit("onResponse", res.data.messages, "success");
            this.variationList = this.variationList.filter(
              (variation) => variation.id !== this.selectedVariation.info.id
            );
            this.selectedVariation = null;

            this.$emit("onVariationsUpdated", this.variationList);
          } else {
            this.$emit("onResponse", res.data.messages, "error");
          }
        })
        .catch((error) => {
          console.error(error);
          this.$emit("onResponse", error.message, "error");
        });
    },

    onResponse(...args) {
      this.$emit("onResponse", ...args);
    },
  },
};
</script>