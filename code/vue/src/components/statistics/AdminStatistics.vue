<script>
import axios from 'axios';
import { ref, onMounted, computed, watch } from 'vue';
import { useVcardsStore } from '../../stores/vcards.js';
import { useTransactionsStore } from '../../stores/transactions.js';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
  setup() {
    const vCardsStore = useVcardsStore();
    const transactionsStore = useTransactionsStore();
    const barChartRef = ref(null);
    const filterParams = ref({
      phone_number: '',
      name: '',
      email: '',
      blocked: '',
      max_debit: '',
    });

    const selectedYear = ref(new Date().getFullYear()); // Default to current year
    const currentYear = new Date().getFullYear();
    const availableYears = ref([...Array(5)].map((_, i) => currentYear - 2 + i)); // Last 5 years
    const totalVcards = computed(() => vCardsStore.totalVcards);
    const vCardsBalanceSum = ref(0);
    const transactions = ref([]);
    const monthlyTransactionCount = ref([]);
    const chartInstance = ref(null);


    const countTransactionsPerMonth = async (year) => {
      const monthlyTransactionCount = Array(12).fill(0); // Array for each month
      transactions.value.forEach((transaction) => {
        const transactionDate = new Date(transaction.date);
        const transactionYear = transactionDate.getFullYear();
        const transactionMonth = transactionDate.getMonth();
        if (transactionYear === year) {
          monthlyTransactionCount[transactionMonth]++;
        }
      });
      return monthlyTransactionCount;
    };

    const fetchTransactions = async () => {
      transactions.value = await transactionsStore.fetchAllTransactionsByDate();
      const currentYear = new Date().getFullYear();
      monthlyTransactionCount.value = await countTransactionsPerMonth(currentYear);
      console.log(monthlyTransactionCount.value);

      const ctx = barChartRef.value.getContext('2d');
      chartInstance.value = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
          datasets: [{
            label: '# of Transactions',
            data: monthlyTransactionCount.value,
            borderWidth: 1
          }]
        },
        options: {
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    };

    const fetchTotalBalance = async () => {
      vCardsBalanceSum.value = await vCardsStore.fetchTotalBalance();
    };

    onMounted(async () => {
      await vCardsStore.fetchVcards(filterParams.value);
      fetchTotalBalance();
      fetchTransactions();
    });

    const updateChart = () => {
      if (barChartRef.value && monthlyTransactionCount.value.length) {
        const ctx = barChartRef.value.getContext('2d');

        if (chartInstance.value) {
          chartInstance.value.destroy();
        }

        chartInstance.value = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
              label: '# of Transactions',
              data: monthlyTransactionCount.value,
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              y: {
                beginAtZero: true
              }
            }
          }
        });
      }
    };

    watch(selectedYear, async (newYear) => {
      console.log(selectedYear);
      monthlyTransactionCount.value = await countTransactionsPerMonth(newYear);
      updateChart();
    });

    return {
      barChartRef,
      totalVcards,
      vCardsBalanceSum,
      selectedYear,
      availableYears,
    };
  }
}

</script>


<template>
  <div class="vcards-stats">
    <h2>vCards Statistics</h2>
    <p>Total Active vCards: {{ totalVcards }}</p>
    <p>Vcards Total Balance: {{ vCardsBalanceSum }}</p>
    <div>
      <label for="year-select">Select Year:</label>
      <select id="year-select" v-model="selectedYear">
        <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
      </select>
    </div>
    <canvas ref="barChartRef"></canvas>
  </div>
</template>

<style scoped>
.vcards-stats {
  margin: 20px;
  padding: 15px;
  background-color: #f2f4f8;
  /* Light grey background */
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  /* subtle shadow for depth */
  text-align: center;
}

.active-vcards-stats h2 {
  color: #333;
  /* Dark text color for the heading */
  margin-bottom: 10px;
}

.active-vcards-stats p {
  color: #555;
  /* Slightly lighter text color for the paragraph */
  font-size: 1.1em;
  /* Slightly larger font size for emphasis */
  font-weight: bold;
}

.year-select {
  margin-bottom: 20px;
}
</style>