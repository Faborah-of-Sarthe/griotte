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
import Toast, {useToast} from "vue-toastification";
import "vue-toastification/dist/index.css";


const app = createApp(App)

const options = {
    hideProgressBar: true,
    transition: "Vue-Toastification__fade",
}; 

app.use(Toast, options);

// Handle the 401 error by logging out the user and reloading the page
axios.interceptors.response.use(
    response => {
        if(response.status === 200 && response.data.message){
            const toast = useToast()
            if(response.data.options) {
                toast.success(response.data.message, response.data.options);
            } else {
                toast.success(response.data.message);
            }
        }
        return Promise.resolve(response)
    },
    error => {
        if (error.response?.status === 401) {
            logout()
            window.location.reload()
        }
        if(error.response?.status === 404){ 
            router.push({name: 'NotFound'})
        }
        if(error.response?.status === 403){ 
            router.push({name: 'NotFound'})
        }
        if(error.response?.data?.message) {
            const toast = useToast()
            toast.error(error.response.data.message);
        }
        return Promise.reject(error)
    },
)

// Axios default headers
axios.defaults.headers.common['Accept'] = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;


const pinia = createPinia()
pinia.use(piniaPluginPersistedState)
app.use(pinia)
app.use(router)
app.use(VueQueryPlugin)
app.directive('longpress', longpressDirective);


app.mount('#app')
