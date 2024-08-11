/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

window.Vue = require('vue').default;

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('users', require('./components/Users.vue').default);
Vue.component('user', require('./components/User.vue').default);
Vue.component('add-user', require('./components/AddUser.vue').default);
Vue.component('edit-user', require('./components/EditUser.vue').default);
Vue.component('delete-user', require('./components/DeleteUser.vue').default);


Vue.component('listing-switch', require('./components/item/index/ListingSwitch.vue').default);

/**
 * Edit Item
 */

Vue.component('edit-item', require('./components/item/edit/EditItem.vue').default);
Vue.component('edit-item-basic-info', require('./components/item/edit/EditItemBasicInfo.vue').default);
Vue.component('edit-item-image-list', require('./components/item/edit/images/EditItemImageList.vue').default);
Vue.component('edit-item-image', require('./components/item/edit/images/EditItemImage.vue').default);
Vue.component('delete-item-image-modal', require('./components/item/edit/images/DeleteItemImageModal.vue').default);
Vue.component('edit-item-category', require('./components/item/edit/EditItemCategory.vue').default);
Vue.component('edit-item-variation-list', require('./components/item/edit/variations/EditItemVariationList.vue').default);
Vue.component('edit-item-variation', require('./components/item/edit/variations/EditItemVariation.vue').default);
Vue.component('edit-item-wholesale-discount-list', require('./components/item/edit/wholesales/EditItemWholesaleDiscountList.vue').default);
Vue.component('edit-item-wholesale-discount', require('./components/item/edit/wholesales/EditItemWholesaleDiscount.vue').default);
Vue.component('util-table', require('./components/item/edit/UtilTable.vue').default);

/**
 * Shared
 */

Vue.component('edit-discount', require('./components/shared/components/EditDiscount.vue').default);
Vue.component('item-variation-modal', require('./components/shared/modals/ItemVariationModal.vue').default);
Vue.component('wholesale-discount-modal', require('./components/shared/modals/WholesaleDiscountModal.vue').default);
Vue.component('upload-image-modal', require('./components/shared/modals/UploadImageModal.vue').default);
Vue.component('message-toast', require('./components/shared/toasts/MessageToast.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app'
});