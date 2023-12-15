<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import { ref, watch, computed, onMounted } from 'vue'
import { useDefaultCategoriesStore } from '../../stores/defaultCategories'
import DefaultCategoryDetail from './DefaultCategoryDetail.vue'

const toast = useToast()
const router = useRouter()
const defaultCategoriesStore = useDefaultCategoriesStore()
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''
const newDefaultCategory = () => {
    return {
        id: null,
        name: '',
        type: '',
    }
}
const defaultCategory = ref(newDefaultCategory())

const loadDefaultCategory = async (id) => {
    originalValueStr = ''
    errors.value = null
    if (!id || (id < 0)) {
        defaultCategory.value = newDefaultCategory()
        originalValueStr = JSON.stringify(defaultCategory.value)
    } else {
        try {
            const response = await axios.get('default_categories/' + id)
            defaultCategory.value = response.data.data
            originalValueStr = JSON.stringify(defaultCategory.value)
        } catch (error) {
            toast.error('Error loading default category #' + id + '!')
        }
    }
}

const save = async () => {
    errors.value = null
    if (operation.value == 'insert') {
        try {
            defaultCategory.value = await defaultCategoriesStore.insertDefaultCategory(defaultCategory.value)
            originalValueStr = JSON.stringify(defaultCategory.value)
            toast.success('Default Category #' + defaultCategory.value.id + ' was created successfully.')
            router.back()
        } catch (error) {
            if (error.response?.status == 422) {
                errors.value = error.response.data.errors
                toast.error('Default Category was not created due to validation errors!')
            } else {
                toast.error(error.response?.data?.message || 'Default Category was not created due to unknown server error!')
            }
        }
    } else {
        try {
            await defaultCategoriesStore.updateDefaultCategory(defaultCategory.value)
            originalValueStr = JSON.stringify(defaultCategory.value)
            toast.success('Category #' + defaultCategory.value.id + ' was updated successfully.')
            router.back()
        } catch (error) {
            if (error.response?.status == 422) {
                errors.value = error.response.data.errors
                toast.error('Category #' + props.id + ' was not updated due to validation errors!')
            } else {
                toast.error(error.response?.data?.message || 'Default Category was not created due to unknown server error!')
            }
        }
    }
}

const cancel = () => {
    originalValueStr = JSON.stringify(defaultCategory)
    router.back()
}

const props = defineProps({
    id: {
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
        loadDefaultCategory(newValue)
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
    let newValueStr = JSON.stringify(defaultCategory.value)
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
    <confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
        msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">
    </confirmation-dialog>

    <default-category-detail :operationType="operation" :defaultCategory="defaultCategory" :errors="errors" @save="save"
        @cancel="cancel"></default-category-detail>
</template>