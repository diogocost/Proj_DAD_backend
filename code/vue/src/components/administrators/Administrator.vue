<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { ref, watch, computed, onMounted } from 'vue'
import AdministratorDetail from "./AdministratorDetail.vue"

const toast = useToast()
const router = useRouter()

const newAdministrator = () => {
  return {
    id: null,
    name: '',
    email:'',
    password: '',
    password_confirmation: '',
  }
}
const administrator = ref(newAdministrator())
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''
  
const loadAdministrator = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || (id < 0)) {
    administrator.value = newAdministrator()
    originalValueStr = JSON.stringify(administrator.value)
  } else {
      try {
        const response = await axios.get('admins/' + id)
        administrator.value = response.data.data
        originalValueStr = JSON.stringify(administrator.value)
      } catch (error) {
        console.log(error)
      }
  }
} 

const save = async () => {
  errors.value = null
  if (operation.value == 'insert') {
    try {
      const response = await axios.post('admins', administrator.value)
      administrator.value = response.data.data
      originalValueStr = JSON.stringify(administrator.value)
      toast.success('Administrator #' + administrator.value.id + ' was created successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Administrator was not created due to validation errors!')
      } else {
        toast.error('Administrator was not created due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(administrator.value)
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
      loadAdministrator(newValue)
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
  let newValueStr = JSON.stringify(administrator.value)
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

  <administrator-detail
    :operationType="operation"
    :administrator="administrator"
    :errors="errors"
    @save="save"
    @cancel="cancel"
  ></administrator-detail>
</template>