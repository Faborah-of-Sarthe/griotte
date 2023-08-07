import './assets/main.scss'

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import  piniaPluginPersistedState  from 'pinia-plugin-persistedstate'

import App from './App.vue'
import router from './router'
import { VueQueryPlugin } from '@tanstack/vue-query'
import axios from 'axios'
import { logout } from './utils'
import longpressDirective from './directives/longpress';

const app = createApp(App)

// Handle the 401 error by logging out the user and reloading the page
axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            logout()
            window.location.reload()
        }
        return Promise.reject(error)
    }
)

// Axios default headers
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.withCredentials = true;


const pinia = createPinia()
pinia.use(piniaPluginPersistedState)
app.use(pinia)
app.use(router)
app.use(VueQueryPlugin)
app.directive('longpress', longpressDirective);


app.mount('#app')
