<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useUserStore } from "../../stores/user.js"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { ref, watch, computed } from 'vue'
import TransactionDetail from "./TransactionDetail.vue"
import { useTransactionsStore } from "../../stores/transactions.js"
import ConfirmationCodeDialog from '../global/ConfirmationCodeDialog.vue'

const toast = useToast()
const router = useRouter()
const userStore = useUserStore()
const transactionsStore = useTransactionsStore()

const newtransaction = () => {
  return {
    id: null,
    vcard: userStore.userIsAdmin ? '' : userStore.userId.toString(),
    pair_vcard: '',
    description: '',
    value: null,
    type: userStore.userIsAdmin ? 'C' : 'D',
    category_id: null,
    payment_type: userStore.userIsAdmin ? '' : 'VCARD',
    payment_reference: '',
    confirmation_code: '',
  }
}
const transaction = ref(newtransaction())
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
const confirmationCodeDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''

const loadTransaction = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || (id < 0)) {
    transaction.value = newtransaction()
    transaction.value.vcard = props.vcardId
    console.log("HERE", transaction)
    originalValueStr = JSON.stringify(transaction.value)
  } else {
    try {
      const response = await axios.get('transactions/' + id)
      transaction.value = response.data.data
      originalValueStr = JSON.stringify(transaction.value)
    } catch (error) {
      toast.error('Error loading transaction #' + id + '!')
    }
  }
}

const save = async () => {
  errors.value = null
  if (operation.value == 'insert') {
    console.log('inserting transaction', transaction.value)
    try {
      if(transaction.value.vcard == null){
        transaction.value.vcard = userStore.userIsAdmin ? '' : userStore.userId.toString()
      }
      transaction.value = await transactionsStore.insertTransaction(transaction.value)
      originalValueStr = JSON.stringify(transaction.value)
      toast.success('Transaction #' + transaction.value.id + ' was created successfully.')
      confirmationCodeDialog.value.hide()
      router.back()
    } catch (error) {
      if (error.response?.status == 422) {
        errors.value = error.response.data.errors
        console.log(error)
        if(!error.response.data.errors.confirmation_code) {
          confirmationCodeDialog.value.hide()
        }
        toast.error('Transaction was not created due to validation errors!')
      } else {
        console.log(error)
        toast.error('Transaction was not created due to unknown server error!')
      }
    }
  } else {
    try {
      transaction.value = await transactionsStore.updateTransaction(transaction.value)
      originalValueStr = JSON.stringify(transaction.value)
      toast.success('Transaction #' + transaction.value.id + ' was updated successfully.')
      router.back()
    } catch (error) {
      console.log(error)
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Transaction #' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error('Transaction #' + props.id + ' was not updated due to unknown server error!')
      }
    }
  }
}

const handleSave = () => {
  if(userStore.userIsAdmin || operation.value != 'insert'){
    save()
  } else {
    confirmationCodeDialog.value.show()
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
  vcardId: {
    type: Number,
    default: null
  },
})


const operation = computed(() => (!props.id || props.id < 0) ? 'insert' : 'update')

// beforeRouteUpdate was not fired correctly
// Used this watcher instead to update the ID
watch(
  () => props.id,
  (newValue) => {
    loadTransaction(newValue)
  },
  { immediate: true }
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
  <confirmation-code-dialog ref="confirmationCodeDialog" :data="transaction" @confirmed="save" :errors="errors" :showPassword="false"
  msg="To send a transactions the you need to provide your confirmation code">
  </confirmation-code-dialog>
  <confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
    msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">
  </confirmation-dialog>

  <transaction-detail :operationType="operation" :transaction="transaction" :errors="errors" @save="handleSave"
    @cancel="cancel"></transaction-detail>
</template>