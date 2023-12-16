import { defineStore } from 'pinia'
import { useVcardsStore } from './vcards'
import { useTransactionsStore } from './transactions'
import { useCategoriesStore } from './categories'
import { useUserStore } from './user'
import { computed } from 'vue'; // Import computed from Vue


export const useDashboardStore = defineStore('dashboard', () => {
  const vcardsStore = useVcardsStore()
  const transactionsStore = useTransactionsStore()
  const categoriesStore = useCategoriesStore()
  const userStore = useUserStore()








  const monthlyStatistics = computed(() => {
    const stats = {}
    transactionsStore.transactions.forEach((transaction) => {
      const month = transaction.date.slice(0, 7) // 'YYYY-MM'
      if (!stats[month]) {
        stats[month] = { gains: 0, spendings: 0 }
      }
      if (transaction.type == 'C') {
        stats[month].gains += parseFloat(transaction.value)
      } else {
        stats[month].spendings += parseFloat(transaction.value)
      }
    })
    return stats
  })

  const yearlyStatistics = computed(() => {
    const stats = {}
    transactionsStore.transactions.forEach((transaction) => {
      const year = transaction.date.slice(0, 4) // Extract the year 'YYYY'

      if (!stats[year]) {
        stats[year] = { gains: 0, spendings: 0 }
      }

      if (transaction.type === 'C') {
        // Assuming 'C' stands for Credit
        stats[year].gains += parseFloat(transaction.value)
      } else {
        // Assuming any other type is Debit
        stats[year].spendings += parseFloat(transaction.value)
      }
    })

    return stats
  })

  const categoryStatistics = computed(() => {
    const stats = {}
    transactionsStore.transactions.forEach((transaction) => {
      const categoryName = categoriesStore.categories.find(
        (c) => c.id === transaction.category_id
      )?.name
      if (!stats[categoryName]) {
        stats[categoryName] = { gains: 0, spendings: 0 }
      }
      if (transaction.type == 'C') {
        stats[categoryName].gains += parseFloat(transaction.value)
      } else {
        stats[categoryName].spendings += parseFloat(transaction.value)
      }
    })
    return stats
  })



  return {
 
    monthlyStatistics,
    yearlyStatistics,
    categoryStatistics
  }
})
