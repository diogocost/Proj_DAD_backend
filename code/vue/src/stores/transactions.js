import axios from 'axios'
import { ref, computed, inject } from 'vue'
import { defineStore } from 'pinia'
import { useUserStore } from "./user.js"
import { useToast } from "vue-toastification"


export const useTransactionsStore = defineStore('transactions', () => {
    const toast = useToast()

    const userStore = useUserStore()

    const transactions = ref([])

    const totalTransactions = computed(() => {
        return transactions.value.length
    })

    const socket = inject('socket')

    function clearTransactions() {
        transactions.value = []
    }

    async function loadTransactions(filters) {
        try {
            const params = { ...filters };; // Create a copy of filterParams

            for (const key in params) {
                if (params[key] === null || params[key] === undefined || params[key] === '') {
                    delete params[key]
                }
            }

            const response = await axios.get(`vcards/${userStore.userId}/transactions`, { params });
            transactions.value = response.data.data

            return transactions.value
        } catch (error) {
            clearTransactions()
            throw error
        }
    }

 

    async function insertTransaction(newTransaction) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the insertTransaction
        const response = await axios.post('transactions/', newTransaction)
        transactions.value.push(response.data.data)
        socket.emit('newTransaction', response.data.data)
        return response.data.data
    }

    async function updateTransaction(updateTransaction) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the updateTransaction
        const response = await axios.patch('transactions/' + updateTransaction.id, updateTransaction)
        let idx = transactions.value.findIndex((t) => t.id === response.data.data.id)
        if (idx >= 0) {
            transactions.value[idx] = response.data.data
        }
        return response.data.data
    }

    async function deleteTransaction(deleteTransaction) {
        // Note that when an error occours, the exception should be
        // catch by the function that called the deleteTransaction
        const response = await axios.delete('transactions/' + deleteTransaction.id)
        let idx = transactions.value.findIndex((t) => t.id === response.data.data.id)
        if (idx >= 0) {
            transactions.value.splice(idx, 1)
        }
        return response.data.data
    }

    async function fetchAllTransactionsByDate() {
        const response = await axios.get('/transactions');
        return response.data.dates;
    }
  
    return {
        transactions,
        totalTransactions,
        loadTransactions,
        clearTransactions,
        insertTransaction,
        updateTransaction,
        deleteTransaction,
        fetchAllTransactionsByDate
    }
})
