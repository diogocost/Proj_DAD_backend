import { createRouter, createWebHistory } from 'vue-router'
//import { useUserStore } from "../stores/user.js"
import HomeView from '../views/HomeView.vue'
//import Dashboard from "../components/Dashboard.vue"
import Login from "../components/auth/Login.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"
import Transactions from "../components/transactions/Transactions.vue"
import Transaction from "../components/transactions/Transaction.vue"
import Categories from "../components/categories/Categories.vue"
import Administrators from "../components/administrators/Administrators.vue"
import Vcard from "../components/vcards/Vcard.vue"
import Vcards from "../components/vcards/Vcards.vue"
import Statistics from "../components/statistics/Statistics.vue"
//import Users from "../components/users/Users.vue"
//import ProjectTasks from "../components/projects/ProjectTasks.vue"
//import Task from "../components/tasks/Task.vue"
//import Project from "../components/projects/Project.vue"

//let handlingFirstRoute = true

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue')
    },
    {
      path: '/login',
      name: 'Login',
      component: Login
    },
    {
      path: '/password',
      name: 'ChangePassword',
      component: ChangePassword
    },
    {
      path: '/vcards/new',
      name: 'NewVcard',
      component: Vcard,
      props: { id: -1 }
    }, 
    {
        path: '/vcards/:id',
        name: 'Vcard',
        component: Vcard,
        //props: true
        // Replaced with the following line to ensure that id is a number
        props: route => ({ id: parseInt(route.params.id) })
    }, 
    {
        path: '/vcards',
        name: 'Vcards',
        component: Vcards,
    },
    {
        path: '/transactions',
        name: 'Transactions',
        component: Transactions,
    },  
    {
        path: '/transactions/new',
        name: 'NewTransactions',
        component: Transaction,
        props: { id: -1 }
      },
    {
        path: '/categories',
        name: 'Categories',
        component: Categories,
    }, 
    {
        path: '/administrators',
        name: 'Administrators',
        component: Administrators,
    },      
    {
      path: '/statistics',
      name: 'Statistics',
      component: Statistics,
    },
  ]
})



export default router