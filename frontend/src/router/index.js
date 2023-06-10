import { createRouter, createWebHistory } from 'vue-router'


const ListView = () => import('../views/ListView.vue')
const HomeView = () => import('../views/HomeView.vue')

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),


  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/my-list',
      name: 'my-list',
      component: ListView
    }
  ]
})

// router.beforeEach((to, from, next) => {
  // if connected, redirect home to my-list
  // if (to.name === 'home' && localStorage.getItem('token')) {
  //   next({ name: 'my-list' })
  // } else {
  //   next()
  // }
// })

export default router
