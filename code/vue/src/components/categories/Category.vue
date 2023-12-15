<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { ref, watch, computed, onMounted } from 'vue'

import CategoryDetail from "./CategoryDetail.vue"

const toast = useToast()
const router = useRouter()

const newCategory = () => {
  return {
    id: null,
    vcard: '',
    type: '',
    name: '',
  }
}
const category = ref(newCategory())
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''
  
const loadCategory = async (id) => {
  originalValueStr = ''
  errors.value = null
  if (!id || (id < 0)) {
    category.value = newCategory()
    originalValueStr = JSON.stringify(category.value)
  } else {
      try {
        const response = await axios.get('categories/' + id)
        category.value = response.data.data
        originalValueStr = JSON.stringify(category.value)
      } catch (error) {
        console.log(error)
      }
  }
}

const save = async () => {
  errors.value = null
  if (operation.value == 'insert') {
    try {
      const response = await axios.post('categories', category.value)
      category.value = response.data.data
      originalValueStr = JSON.stringify(category.value)
      toast.success('Category #' + category.value.id + ' was created successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Category was not created due to validation errors!')
      } else {
        toast.error(error.response?.data?.message || 'Category was not created due to unknown server error!')
      }
    }
  } else {
    try {
      const response = await axios.put('categories/' + props.id, category.value)
      category.value = response.data.data
      originalValueStr = JSON.stringify(category.value)
      toast.success('Category #' + category.value.id + ' was updated successfully.')
      router.back()
    } catch (error) {
      if (error.response.status == 422) {
        errors.value = error.response.data.errors
        toast.error('Category #' + props.id + ' was not updated due to validation errors!')
      } else {
        toast.error(error.response?.data?.message || 'Category #' + props.id + 'was not created due to unknown server error!')
      }
    }
  }
}

const cancel = () => {
  originalValueStr = JSON.stringify(category.value)
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
      loadCategory(newValue)
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
  let newValueStr = JSON.stringify(category.value)
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

  <category-detail
    :operationType="operation"
    :category="category"
    :errors="errors"
    @save="save"
    @cancel="cancel"
  ></category-detail>
</template>