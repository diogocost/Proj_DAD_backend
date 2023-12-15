// useDashboardStore.js
import { defineStore } from 'pinia'
import { computed } from 'vue'
import { useTransactionsStore } from './transactions'
import { useVcardsStore } from './vcards' // Import useVcardsStore

export const useDashboardStore = defineStore('dashboard', () => {
  const transactionsStore = useTransactionsStore()
  const vcardsStore = useVcardsStore() // Initialize vcardsStore

  // Helper function to filter transactions by date
  const filterTransactionsByDate = (transactions, start, end) => {
    return transactions.filter((transaction) => {
      const transactionDate = new Date(transaction.date)
      return transactionDate >= start && transactionDate <= end
    })
  }

  const dailySpendings = computed(() => {
    if (
      !transactionsStore.transactions.value ||
      transactionsStore.transactions.value.length === 0
    ) {
      return 0
    }
    const today = new Date()
    const startOfDay = new Date(today.getFullYear(), today.getMonth(), today.getDate())
    const endOfDay = new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1)
    const todayTransactions = filterTransactionsByDate(
      transactionsStore.transactions.value,
      startOfDay,
      endOfDay
    )
    return todayTransactions.reduce((total, transaction) => total + transaction.value, 0)
  })

  const monthlySpendings = computed(() => {
    if (
      !transactionsStore.transactions.value ||
      transactionsStore.transactions.value.length === 0
    ) {
      return 0
    }
    const today = new Date()
    const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
    const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0)
    const monthTransactions = filterTransactionsByDate(
      transactionsStore.transactions.value,
      startOfMonth,
      endOfMonth
    )
    return monthTransactions.reduce((total, transaction) => total + transaction.value, 0)
  })

  const yearlySpendings = computed(() => {
    if (
      !transactionsStore.transactions.value ||
      transactionsStore.transactions.value.length === 0
    ) {
      return 0
    }
    const today = new Date()
    const startOfYear = new Date(today.getFullYear(), 0, 1)
    const endOfYear = new Date(today.getFullYear() + 1, 0, 0)
    const yearTransactions = filterTransactionsByDate(
      transactionsStore.transactions.value,
      startOfYear,
      endOfYear
    )
    return yearTransactions.reduce((total, transaction) => total + transaction.value, 0)
  })

  const currentBalance = computed(() => {
    if (vcardsStore.vcards && vcardsStore.vcards.value && vcardsStore.vcards.value.length > 0) {
      return vcardsStore.vcards.value[0].balance
    }
    return 0
  })

  return {
    dailySpendings,
    monthlySpendings,
    yearlySpendings,
    currentBalance
  }
})
