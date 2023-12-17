<script setup>
import { useUserStore } from '../../stores/user.js';
import { useDashboardStore } from '../../stores/dashboard.js';
import { useTransactionsStore } from '../../stores/transactions.js';
import { onMounted, computed, ref, watch } from 'vue';
import { useVcardsStore } from '../../stores/vcards';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);


const userStore = useUserStore();
const dashboardStore = useDashboardStore();
const transactionsStore = useTransactionsStore();
const vcardsStore = useVcardsStore();

const vcardBalance = ref(0);
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
const totalVcards = computed(() => vcardsStore.totalVcards);
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
        label: 'Number of Transactions per Month',
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
  vCardsBalanceSum.value = await vcardsStore.fetchTotalBalance();
};

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
          label: 'Number of Transactions per Month',
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

const sortedCategoryStatistics = computed(() => {
  return Object.entries(dashboardStore.categoryStatistics)
    .sort((a, b) => a[0].localeCompare(b[0]))
    .reduce((obj, [key, value]) => ({ ...obj, [key]: value }), {});
});

const sortedMonthlyStatistics = computed(() => {
  return Object.entries(dashboardStore.monthlyStatistics)
    .sort((a, b) => new Date(a[0]) - new Date(b[0]))
    .reduce((obj, [key, value]) => ({ ...obj, [key]: value }), {});
});

const sortedYearlyStatistics = computed(() => {
  return Object.entries(dashboardStore.yearlyStatistics)
    .sort((a, b) => a[0] - b[0]) // Assuming the format is YYYY
    .reduce((obj, [key, value]) => ({ ...obj, [key]: value }), {});
});

onMounted(async () => {
  if (userStore.userIsAdmin) {
    await vcardsStore.fetchVcards(filterParams.value);
    fetchTotalBalance();
    fetchTransactions();
  }
  if (!userStore.userIsAdmin) {
    await transactionsStore.loadTransactions();

    // Fetch the vCard for the current user
    try {
      const vcard = await vcardsStore.loadVcard(userStore.userId); // Replace with the correct identifier
      vcardBalance.value = vcard.data.balance; // Replace 'balance' with the actual property name in your vcard object

      console.log("vcardBalance:", vcard);
    } catch (error) {
      console.error("Error fetching vCard:", error);
      // Handle error appropriately
    }
  }
});
</script>


<template>
  <div class="dashboard">
    <h1>Dashboard</h1>

    <div v-if="userStore.userIsAdmin">
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
    </div>
    <div v-else>
      <p class="userarea">This is your user dashboard area.</p>
      <p class="currentbalance">Current user balance: {{ vcardBalance }} €</p>
      <div class="statistics-container">
        <!-- Category Statistics Table -->
        <div class="table-container">
          <h5>Category Statistics</h5>
          <table class="table-header">
            <thead>
              <tr>
                <th>Category</th>
                <th>Debit</th>
                <th>Credit</th>
              </tr>
            </thead>
          </table>
          <div class="table-body">
            <table>
              <tbody>
                <tr v-for="(value, category) in sortedCategoryStatistics" :key="category">
                  <td>{{ category }}</td>
                  <td>{{ value.spendings.toFixed(2) }}</td>
                  <td>{{ value.gains.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="separator-line"></div> <!-- Separator line between tables -->
        <!-- Monthly Statistics Table -->
        <div class="table-container">
          <h5>Monthly Statistics</h5>
          <table class="table-header">
            <thead>
              <tr>
                <th>Month</th>
                <th>Spendings (€)</th>
                <th>Gains (€)</th>
              </tr>
            </thead>
          </table>
          <div class="table-body">
            <table>
              <tbody>
                <tr v-for="(value, month) in sortedMonthlyStatistics" :key="month">
                  <td>{{ month }}</td>
                  <td>{{ value.spendings.toFixed(2) }}</td>
                  <td>{{ value.gains.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="separator-line"></div> <!-- Separator line between tables -->
        <!-- Yearly Statistics Table -->
        <div class="table-container">
          <h5>Yearly Statistics</h5>
          <table class="table-header">
            <thead>
              <tr>
                <th>Year</th>
                <th>Spendings (€)</th>
                <th>Gains (€)</th>
              </tr>
            </thead>
          </table>
          <div class="table-body">
            <table>
              <tbody>
                <tr v-for="(value, year) in sortedYearlyStatistics" :key="year">
                  <td>{{ year }}</td>
                  <td>{{ value.spendings.toFixed(2) }}</td>
                  <td>{{ value.gains.toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>


<style>
.vcards-stats {
  width: 70vw;
  height: 80vw;
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

.dashboard {
  min-height: 100vh;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
}

.userarea {
  font-size: 14px;

  margin-bottom: 20px;
  margin-left: 43.2%;
}

.currentbalance {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 20px;
  margin-left: 41%;
}

.statistics-container {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  width: 100%;
  gap: 30px;
  /* Adds space between the tables */
}

/* Table container styling */
.table-container {
  margin-bottom: 10px;
  border-radius: 8px;
  /* Rounded corners */
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  /* Optional: Adds subtle shadow */
  overflow: hidden;
  /* Ensures the rounded corners affect inner table */
  position: relative;
  /* Needed to position the separator line */
}

.separator-line {
  height: 1px;
  background-color: #ddd;
  /* Color of the separator line */
}

.table-container::after {
  content: '';
  position: absolute;
  bottom: -20px;
  left: 0;
  width: 100%;
  height: 1px;
  background-color: #ddd;
  /* Color of the separator line */
}

/* Header styles */
.table-header {
  background-color: #f2f2f2;
  width: 100%;
  table-layout: fixed;
  border-top-left-radius: 8px;
  /* Rounded top corners */
  border-top-right-radius: 8px;
  /* Rounded top corners */
}

.table-header th {
  padding: 8px;
  text-align: left;
  /* Removed border from right of the header cells */
  border-bottom: 1px solid #ddd;
  /* Only keep the bottom border */
}

.table-header th:last-child {
  border-right: none;
}

/* Body styles */
.table-body {
  max-height: 188px;
  /* Adjusted for 6 rows approximately */
  overflow-y: auto;
  border-bottom-left-radius: 8px;
  /* Rounded bottom corners */
  border-bottom-right-radius: 8px;
  /* Rounded bottom corners */
}

.table-body table {
  border-collapse: separate;
  width: 100%;
  border-spacing: 0;
  /* Removes any space between borders */
}

.table-container td {
  padding: 8px;
  text-align: left;
  border-right: 1px solid #ddd;
  border-bottom: 1px solid #ddd;
}

.table-body tr:last-child td {
  border-bottom: none;
}

.table-container tr td:last-child {
  border-right: none;
}

/* Scrollbar styling */
.table-body::-webkit-scrollbar {
  width: 8px;
}

.table-body::-webkit-scrollbar-thumb {
  background-color: #888;
  border-radius: 4px;
}

/* Set specific widths for each column */
.table-container th:nth-child(1),
.table-container td:nth-child(1),
.table-container th:nth-child(2),
.table-container td:nth-child(2),
.table-container th:nth-child(3),
.table-container td:nth-child(3) {
  width: 33.33%;
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
  /* Add responsive design adjustments here if needed */
}
</style>
