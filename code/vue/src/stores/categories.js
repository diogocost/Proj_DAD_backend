import axios from 'axios'
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useUserStore } from "./user.js"
import { inject } from 'vue'
import { useToast } from "vue-toastification"


export const useCategoriesStore = defineStore('categories', () => {
    const toast = useToast()

    const userStore = useUserStore()

    const categories = ref([])

    const categoriesDebit = ref([])

    const categoriesCredit = ref([])

    const totalCategories = computed(() => {
        return categories.value.length
    })

    function clearCategories() {
        categories.value = []
    }

    async function loadCategories() {
        try {
            const response = await axios.get(`vcards/${userStore.userId}/categories`);
            categories.value = response.data.data

            categoriesDebit.value = categories.value.filter((c) => c.type === 'D')
            categoriesCredit.value = categories.value.filter((c) => c.type === 'C')

            return categories.value
        } catch (error) {
            clearCategories()
            throw error
        }
    }

    async function insertCategorie(newCategorie) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the insertCategorie
        const response = await axios.post('categories', newCategorie)
        categories.value.push(response.data.data)

        socket.emit('newCategorie', response.data.data);

        return response.data.data
    }

    async function updateCategorie(updateCategorie) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the updateCategorie
        const response = await axios.put('categories/' + updateCategorie.id, updateCategorie)
        let idx = categories.value.findIndex((t) => t.id === response.data.data.id)
        if (idx >= 0) {
            categories.value[idx] = response.data.data
        }
        return response.data.data
    }

    async function deleteCategorie(deleteCategorie) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the deleteCategorie
        const response = await axios.delete('categories/' + deleteCategorie.id)
        let idx = categories.value.findIndex((t) => t.id === response.data.data.id)
        if (idx >= 0) {
            categories.value.splice(idx, 1)
        }
        return response.data.data
    }

    return {
        categories,
        categoriesDebit,
        categoriesCredit,
        totalCategories,
        loadCategories,
        clearCategories,
        insertCategorie,
        updateCategorie,
        deleteCategorie
    }
})
