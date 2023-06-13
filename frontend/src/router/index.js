import { createRouter, createWebHistory } from 'vue-router'
import { isLoggedIn } from '../utils'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),


  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('../views/HomeView.vue'),
      meta: {
        middleware: "guest",
        class: "no-header"
      }
    },
    {
      path:'/login',
      name: 'login',
      component: () => import('../views/LoginView.vue'),
      meta: {
        middleware: "guest",
        class: "red-bg"
      }
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: {
        middleware: "guest",
        class: "red-bg"
      }
    },
    {
      path: '/my-list',
      name: 'my-list',
      component: () => import('../views/ListView.vue'),
      meta: {
        middleware: "auth"
      }
    }
  ]
})

// Handle auth for each route
router.beforeEach((to, from, next) => { 
  // Check if the route has a middleware auth and if the user is logged in
  if (to.meta.middleware === 'auth' && !isLoggedIn()) {
    next({ name: 'login' })
    return
  }
  // Check if the route has a middleware guest and if the user is logged in
  if (to.meta.middleware === 'guest' && isLoggedIn()) {
    next({ name: 'my-list' })
    return
  }

  next()
  return

})

export default router
