<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user.js"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { ref, watch, computed } from 'vue'

import TransactionDetail from "./TransactionDetail.vue"
import { useTransactionsStore } from "../../stores/transactions.js"
import { useCategoriesStore } from '../../stores/categories'

const toast = useToast()
const router = useRouter()
const userStore = useUserStore()
const transactionsStore = useTransactionsStore()
const categoriesStore = useCategoriesStore()

const newtransaction = () => {
  return {
    id: null,
    vcard: userStore.userId,
    pair_vcard: '',
    description: '',
    value: null,
    type: null,
    category_id: null,
    payment_type: '',
    payment_reference: ''
  }
}
const transaction = ref(newtransaction())
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
let originalValueStr = ''
  
const loadTransaction = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || (id < 0)) {
    transaction.value = newtransaction()
    originalValueStr = JSON.stringify(transaction.value)
  } else {
      try {
        const response = await axios.get('transactions/' + id)
        transaction.value = response.data.data
        originalValueStr = JSON.stringify(transaction.value)
      } catch (error) {
        console.log(error)
      }
  }
}

const save = async () => {
  errors.value = null
  if (operation.value == 'insert') {
    console.log('inserting transaction', transaction.value)
    try {
      transaction.value = await transactionsStore.insertTransaction(transaction.value)
      originalValueStr = JSON.stringify(transaction.value)
      toast.success('Transaction #' + transaction.value.id + ' was created successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Transaction was not created due to validation errors!')
      } else {
        toast.error('Transaction was not created due to unknown server error!')
      }
    }
  } else {
    console.log('Updating transaction', transaction.value)
    try {
      transaction.value = await transactionsStore.updateTransaction(transaction.value)
      originalValueStr = JSON.stringify(transaction.value)
      toast.success('Transaction #' + transaction.value.id + ' was updated successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Transaction #' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error('Transaction #' + props.id + ' was not updated due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(transaction.value)
  router.back()
}

const props = defineProps({
  id: {
    type: Number,
    default: null
  },
})


const operation = computed( () => (!props.id || props.id < 0) ? 'insert' : 'update')

  // beforeRouteUpdate was not fired correctly
  // Used this watcher instead to update the ID
watch(
  () => props.id,
  (newValue) => {
      loadTransaction(newValue)
      categoriesStore.loadCategories()
    }, 
  { immediate: true}
)

let nextCallBack = null
const leaveConfirmed = () => {
  if (nextCallBack) {
    nextCallBack()
  }
}

onBeforeRouteLeave((to, from, next) => {
  nextCallBack = null
  let newValueStr = JSON.stringify(transaction.value)
  if (originalValueStr != newValueStr) {
    // Some value has changed - only leave after confirmation
    nextCallBack = next
    confirmationLeaveDialog.value.show()
  } else {
    // No value has changed, so we can leave the component without confirming
    next()
  }
})
</script>


<template>
  <confirmation-dialog
    ref="confirmationLeaveDialog"
    confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!"
    @confirmed="leaveConfirmed"
  >
  </confirmation-dialog>  

  <transaction-detail
    :operationType="operation"
    :transaction="transaction"
    :categories="categoriesStore.categories"
    :errors="errors"
    @save="save"
    @cancel="cancel"
  ></transaction-detail>
</template>