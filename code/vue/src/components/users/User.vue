<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useUserStore } from '../../stores/user.js'
import { ref, watch, inject } from 'vue'
import UserDetail from "./UserDetail.vue"
import { useRouter, onBeforeRouteLeave } from 'vue-router'
import ConfirmationDialog from '../global/ConfirmationDialog.vue'
import { useVcardsStore } from '../../stores/vcards.js'
import ConfirmationCodeDialog from '../global/ConfirmationCodeDialog.vue'

const toast = useToast()
const router = useRouter()
const userStore = useUserStore()
const vcardsStore = useVcardsStore()
const deleteConfirmationData = ref({
    password: '',
    confirmation_code: ''
})
const socket = inject("socket")
const props = defineProps({
    id: {
        type: Number,
        default: null
    }
})

const user = ref(userStore.user)
const errors = ref(null)
const codeDialog = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''

const save = async (userToSave) => {
    errors.value = null
    const type = userStore.userIsAdmin ? 'Admin' : 'Vcard'
    try {
        await userStore.updateUser(userToSave.id, userToSave)
        originalValueStr = JSON.stringify(user.value)
        toast.success(type + ' #' + userStore.userId + ' was updated successfully.')
        //socket.emit('updatedUser', user.value)
        router.back()
    } catch (error) {
        if (error.response?.status == 422) {
            errors.value = error.response.data.errors
            toast.error(type + ' #' + userStore.userId + ' was not updated due to validation errors!')
        } else {
            toast.error(type + ' #' + userStore.userId + ' was not updated due to unknown server error!')
        }
    }
}

const cancel = () => {
    originalValueStr = JSON.stringify(user.value)
    router.back()
}

const dismiss = async () => {
    errors.value = null
    if (userStore.userIsAdmin) {
        try {
            await axios.delete('admins/' + userStore.userId)
            toast.success('Admin #' + userStore.userId + ' was deleted successfully.')
            socket.emit('deletedUser', userStore.userId)
            userStore.clearUser()
            originalValueStr = JSON.stringify(user.value)
            router.back()
        } catch (error) {
            toast.error('Admin  #' + userStore.userId + ' was not deleted due to unknown server error!')
        }
    } else {
        try {
            const userId = userStore.userId
            await vcardsStore.deleteVcard(userStore.userId, deleteConfirmationData.value)
            codeDialog.value.hide()
            socket.emit('deletedUser', userId)
            toast.success('Vcard #' + userId + ' was deleted successfully.')
            originalValueStr = JSON.stringify(user.value)
            router.back()
        } catch (error) {
            console.log(error)
            if (error.response?.status == 422) {
                errors.value = error.response.data.errors
                toast.error('Vcard  #' + userStore.userId + ' was not deleted due to validation errors!')
            } else {
                toast.error('Vcard  #' + userStore.userId + ' was not deleted due to unknown server error!')
            }
        }
    }
}

const handleDismiss = () => {
    if (userStore.userIsAdmin) {
        dismiss()
    } else {
        codeDialog.value.show()
    }
}

let nextCallBack = null
const leaveConfirmed = () => {
    if (nextCallBack) {
        nextCallBack()
    }
}

onBeforeRouteLeave((to, from, next) => {
    nextCallBack = null
    let newValueStr = JSON.stringify(user.value)
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
    <confirmation-code-dialog ref="codeDialog" :data="deleteConfirmationData" @confirmed="dismiss"
        :errors="errors" :showPassword="true" title="Insert password and code">
    </confirmation-code-dialog>
    <confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
        msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">
    </confirmation-dialog>

    <user-detail :user="user" :errors="errors" @save="save" @cancel="cancel" @dismiss="handleDismiss"></user-detail>

</template>
