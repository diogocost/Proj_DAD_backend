<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useUserStore } from '../../stores/user.js'
import { ref, watch} from 'vue'
import VcardDetail from "./VcardDetail.vue"
import { useRouter, onBeforeRouteLeave } from 'vue-router'

const toast = useToast()
const router = useRouter()
const userStore = useUserStore()

const props = defineProps({
    id: {
      type: Number,
      default: null
    }
})

const newVcard = () => {
    return {
      phone_number: '',
      name: '',
      email: '',
      photo_url: null,
      password: '',
      password_confirmation: '',
      confirmation_code: '',
      confirmation_code_confirmation: ''
    }
}

const vcard = ref(newVcard())
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''

const inserting = (id) => !id || (id < 0)
const loadUser = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (inserting(id)) {
    vcard.value = newVcard()
  } else {
      try {
        const response = await axios.get('users/' + id)
        vcard.value = response.data.data
        originalValueStr = JSON.stringify(vcard.value)
      } catch (error) {
        console.log(error)
      }
  }
}

const save = async (userToSave) => {
  errors.value = null
  if (inserting(props.id)) {
    try {
      const response = await axios.post('auth/register', userToSave)
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
      toast.success('Vcard ' + vcard.value.phone_number + ' was registered successfully.')
      await userStore.login({
        username: userToSave.phone_number,
        password: userToSave.password
      })
      router.push({name: 'home'})
    } catch (error) {
        console.log(error)
      if (error.response?.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Vcard was not registered due to validation errors!')
      } else {
        toast.error('Vcard was not registered due to unknown server error!')
      }
    }
  } else {
    try {
      const response = await axios.put('users/me')
      vcard.value = response.data.data
      originalValueStr = JSON.stringify(vcard.value)
      toast.success('Vcard ' + vcard.value.id + ' was updated successfully.')
      if (vcard.value.phone_number == userStore.userId) {
        await userStore.loadUser()
      }
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Vcard ' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error('Vcard ' + props.id + ' was not updated due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(vcard.value)
  router.back()
}

watch(
  () => props.id,
  (newValue) => {
      loadUser(newValue)
    },
  {immediate: true}
)

let nextCallBack = null
const leaveConfirmed = () => {
  if (nextCallBack) {
    nextCallBack()
  }
}

onBeforeRouteLeave((to, from, next) => {
  nextCallBack = null
  let newValueStr = JSON.stringify(vcard.value)
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

  <vcard-detail
    :user="vcard"
    :errors="errors"
    :inserting="inserting(id)"
    @save="save"
    @cancel="cancel"
  ></vcard-detail>
</template>
