import axios from 'axios'
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useUserStore } from "./user.js"
import { inject } from 'vue'
import { useToast } from "vue-toastification"


export const useDefaultCategoriesStore = defineStore('defaultCategories', () => {

    const userStore = useUserStore()

    const defaultCategories = ref([])

    const totalDefaultCategories = computed(() => {
        return filterDefaultCategories.value.length
    })

    const filterParams = ref({
        type: ''
    })

    const filterDefaultCategories = computed(() => {
        if (filterParams.value.type === '') {
            return defaultCategories.value
        } else if (filterParams.value.type === 'D') {
            return defaultCategories.value.filter((c) => c.type === 'D')
        } else if (filterParams.value.type === 'C') {
            return defaultCategories.value.filter((c) => c.type === 'C')
        }
    })

    function clearCategories() {
        defaultCategories.value = []
    }

    async function loadDefaultCategories() {
        try {
            const response = await axios.get(`default_categories/`);
            defaultCategories.value = response.data.data

            return defaultCategories.value
        } catch (error) {
            clearCategories()
            throw error
        }
    }

    async function insertDefaultCategory(newDefaultCategorie) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the insertCategorie
        const response = await axios.post('default_categories', newDefaultCategorie)
        defaultCategories.value.push(response.data.data)

        return response.data.data
    }

    async function updateDefaultCategory(updateDefaultCategorie) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the updateCategorie
        const response = await axios.put('default_categories/' + updateDefaultCategorie.id, updateDefaultCategorie)
        let idx = defaultCategories.value.findIndex((t) => t.id === response.data.data.id)
        if (idx >= 0) {
            defaultCategories.value[idx] = response.data.data
        }
        return response.data.data
    }

    async function deleteDefaultCategory(deleteDefaultCategorie) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the deleteCategorie
        const response = await axios.delete('default_categories/' + deleteDefaultCategorie.id)
        let idx = defaultCategories.value.findIndex((t) => t.id === response.data.data.id)
        if (idx >= 0) {
            defaultCategories.value.splice(idx, 1)
        }
        return response.data.data
    }

    return {
        defaultCategories,
        totalDefaultCategories,
        filterParams,
        filterDefaultCategories,
        loadDefaultCategories,
        clearCategories,
        insertDefaultCategory,
        updateDefaultCategory,
        deleteDefaultCategory
    }
})
