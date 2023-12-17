import { createRouter, createWebHistory } from 'vue-router'
import { useUserStore } from "../stores/user.js"
import HomeView from '../views/HomeView.vue'
//import Dashboard from "../components/Dashboard.vue"
import Login from "../components/auth/Login.vue"
import User from "../components/users/User.vue"
import ChangePassword from "../components/auth/ChangePassword.vue"
import ChangeConfirmationCode from "../components/auth/ChangeConfirmationCode.vue"
import Transactions from "../components/transactions/Transactions.vue"
import Transaction from "../components/transactions/Transaction.vue"
import Categories from "../components/categories/Categories.vue"
import Category from "../components/categories/Category.vue"
import DefaultCategories from "../components/default_categories/DefaultCategories.vue"
import DefaultCategory from "../components/default_categories/DefaultCategory.vue"
import Administrators from "../components/administrators/Administrators.vue"
import Administrator from "../components/administrators/Administrator.vue"
import Vcard from "../components/vcards/Vcard.vue"
import Vcards from "../components/vcards/Vcards.vue"
import Dashboard from "../components/dashboard/Dashboard.vue"

let handlingFirstRoute = true

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: Dashboard
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
      path: '/confirmation_code',
      name: 'ChangeConfirmationCode',
      component: ChangeConfirmationCode
    },
    {
      path: '/user',
      name: 'User',
      component: User
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
      name: 'NewTransaction',
      component: Transaction,
      props: { id: -1 }
    },
    {
      path: '/transactions/:id',
      name: 'Transaction',
      component: Transaction,
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/transactions/addCredit',
      name: 'NewTransactionAddCredit',
      component: Transaction,
      props: route => ({ vcardId: parseInt(route.query.vcard_id) })
    },
    {
      path: '/categories',
      name: 'Categories',
      component: Categories,
    },
    {
      path: '/categories/:id',
      name: 'Category',
      component: Category,
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/categories/new',
      name: 'NewCategories',
      component: Category,
      props: { id: -1 }
    },
    {
      path: '/default_categories',
      name: 'DefaultCategories',
      component: DefaultCategories,
    },
    {
      path: '/default_categories/:id',
      name: 'DefaultCategory',
      component: DefaultCategory,
      props: route => ({ id: parseInt(route.params.id) })
    },
    {
      path: '/default_categories/new',
      name: 'NewDefaultCategory',
      component: DefaultCategory,
      props: { id: -1 }
    },
    {
      path: '/administrators',
      name: 'Administrators',
      component: Administrators,
    },
    {
      path: '/administrators/new',
      name: 'NewAdministrators',
      component: Administrator,
    },
  ]
})

router.beforeEach(async (to, from, next) => {
  const userStore = useUserStore()
  if (handlingFirstRoute) {
    handlingFirstRoute = false
    await userStore.restoreToken()
  }
  if(to.name == 'Login' && userStore.user){
    next({ name: 'home' })
    return
  }
  if ((to.name == 'Login') || (to.name == 'home') || (to.name == 'NewVcard')) {
    next()
    return
  }
  if (!userStore.user) {
    next({ name: 'Login' })
    return
  }
  if (to.name == 'Administrators' || to.name == 'Administrator' || to.name == 'NewAdministrators' || to.name == 'Vcards'
      || to.name == 'DefaultCategories' || to.name == 'DefaultCategory' || to.name == 'NewDefaultCategory') {
    if (userStore.user.user_type != 'A') {
      next({ name: 'home' })
      return
    }
  }
  if (to.name == 'Categories' || to.name == 'Transactions' || to.name == 'ChangeConfirmationCode' || to.name == 'Category' || to.name == 'NewCategory') {
    if (userStore.user.user_type != 'V') {
      next({ name: 'home' })
      return
    }
  }
  next()
})



export default router