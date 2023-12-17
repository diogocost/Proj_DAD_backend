import axios from 'axios'
import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import avatarNoneUrl from '@/assets/avatar-none.png'
import { useCategoriesStore } from "./categories.js"
import { useDefaultCategoriesStore } from "./defaultCategories.js"

export const useUserStore = defineStore('user', () => {
    
    const serverBaseUrl = inject('serverBaseUrl')
    const user = ref(null)
    const userName = computed(() => user.value?.name ?? 'Anonymous')
    const userId = computed(() => user.value?.id ?? -1)
    const userIsAdmin = computed(() => user.value?.user_type == 'A')
    const categoriesStore = useCategoriesStore()
    const defaultCategoriesStore = useDefaultCategoriesStore() 
    const socket = inject('socket')

    const userPhotoUrl = computed(() =>
        user.value?.photo_url
            ? serverBaseUrl + '/storage/fotos/' + user.value.photo_url
            : avatarNoneUrl)

    async function loadUser() {
        try {
            const response = await axios.get('users/me')
            user.value = response.data.data
            if(user.value.user_type == 'A'){
                await defaultCategoriesStore.loadDefaultCategories()
            } else if (user.value.user_type == 'V'){
                await categoriesStore.loadCategories()
            }
            socket.emit('loggedIn', user.value)
        } catch (error) {
            clearUser()
            throw error
        }
    }

    async function login(credentials) {
        try {
            const response = await axios.post('auth/login', credentials)
            axios.defaults.headers.common.Authorization = "Bearer " + response.data.access_token
            sessionStorage.setItem('token', response.data.access_token)
            await loadUser()
            return true
        }
        catch (error) {
            clearUser()
            throw error
        }
    }
    async function logout() {
        try {
            await axios.post('auth/logout')
            socket.emit('loggedOut', user.value)
            clearUser()
            return true
        } catch (error) {
            return false
        }
    }

    function clearUser() {
        delete axios.defaults.headers.common.Authorization
        sessionStorage.removeItem('token')
        user.value = null
        categoriesStore.clearCategories()
    }

    async function restoreToken() {
        let storedToken = sessionStorage.getItem('token')
        if (storedToken) {
            axios.defaults.headers.common.Authorization = "Bearer " + storedToken
            await loadUser()
            return true
        }
        clearUser()
        return false
    }

    async function changeConfirmationCode(fields){
        if(userIsAdmin.value){
            throw new Error('User is an administrator')
        }
        try {
            const response = await axios.patch('vcards/' + userId.value + '/confirmation_code', fields)
            return response.data.data
        } catch (error) {
            throw error
        }
    
    }

    async function changePassword(fields){
        try {
            const response = await axios.patch('users/' + userId.value + '/password', fields)
            return response.data.data
        } catch (error) {
            throw error
        }
    
    }

    async function updateUser(userToUpdateId, userToUpdateData) {
        try {
            const response = await axios.put('users/' + userToUpdateId, userToUpdateData)
            if (userToUpdateId == userId.value) {
                await loadUser()
            }
            return response.data.data
        } catch (error) {
            throw error
        }
    }

    return { user, userId, userIsAdmin, userName, userPhotoUrl, loadUser, clearUser, login, logout, restoreToken, changeConfirmationCode, changePassword, updateUser }
})