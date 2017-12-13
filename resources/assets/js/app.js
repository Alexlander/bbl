
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

import 'bootstrap/dist/css/bootstrap.min.css';
window.Vue = require('vue');
window.axios = require('axios');

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('breadcrumb', require('./components/Breadcrumb.vue'));
Vue.component('cohort', require('./components/Cohort.vue'));
Vue.component('module', require('./components/Module.vue'));
Vue.component('moduleform', require('./components/ModuleForm.vue'));

const app = new Vue({
    el: '#app'
});
