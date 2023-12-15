<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { useUserStore } from '../../stores/user.js'
import { ref, watch, inject } from 'vue'
import UserDetail from "./UserDetail.vue"
import { useRouter, onBeforeRouteLeave } from 'vue-router'

const toast = useToast()
const router = useRouter()
const userStore = useUserStore()
//const socket = inject("socket")

const props = defineProps({
    id: {
        type: Number,
        default: null
    }
})

const user = ref(userStore.user)
const errors = ref(null)
const confirmationLeaveDialog = ref(null)
// String with the JSON representation after loading the project (new or edit)
let originalValueStr = ''

const save = async (userToSave) => {
    errors.value = null
    try {
        const response = await axios.put('users/' + props.id, userToSave)
        user.value = response.data.data
        originalValueStr = JSON.stringify(user.value)
        toast.success('User #' + user.value.id + ' was updated successfully.')
        if (user.value.id == userStore.userId) {
            await userStore.loadUser()
        }
        //socket.emit('updatedUser', user.value)
        router.back()
    } catch (error) {
        if (error.response.status == 422) {
            errors.value = error.response.data.errors
            toast.error('User #' + props.id + ' was not updated due to validation errors!')
        } else {
            toast.error('User #' + props.id + ' was not updated due to unknown server error!')
        }
    }
}

const cancel = () => {
    originalValueStr = JSON.stringify(user.value)
    router.back()
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
    <confirmation-dialog ref="confirmationLeaveDialog" confirmationBtn="Discard changes and leave"
        msg="Do you really want to leave? You have unsaved changes!" @confirmed="leaveConfirmed">
    </confirmation-dialog>

    <user-detail :user="user" :errors="errors" @save="save" @cancel="cancel"></user-detail>
</template>
