// assets/js/app.js
/*
import Vue from 'vue';
import BootstrapVue from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.use(BootstrapVue);

var app = new Vue({
    el: '#app',
    data: {
        message: 'Hello Vue!'
    },
});*/


require('../css/app.scss');

var $ = require('jquery');
require('bootstrap');

$(document).ready(function() {
    $('[data-toggle="popover"]').popover();
});