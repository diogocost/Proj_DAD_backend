<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useUserStore } from "../../stores/user.js"
import { ref, computed, onMounted, watch } from 'vue'
import TransactionTable from "./TransactionTable.vue"
import { useTransactionsStore } from "../../stores/transactions.js"
import { useCategoriesStore } from '../../stores/categories'

const router = useRouter()
const userStore = useUserStore()
const transactionsStore = useTransactionsStore()
const categoriesStore = useCategoriesStore()
const filterParams = ref({
  start_date: '',
  end_date: '',
  transaction_type: '',
  category_id: '',
  pair_vcard: '',
  payment_type: '',
  min_value: '',
  max_value: '',
})

const addTransaction = () => {
  router.push({ name: 'NewTransaction' })
}

const editTransaction = (transaction) => {
  router.push({ name: 'Transaction', params: { id: transaction.id } })
}

const props = defineProps({
  TransactionsTitle: {
    type: String,
    default: 'Transactions'
  },
})

onMounted(() => {
  categoriesStore.loadCategories()
  transactionsStore.loadTransactions({...filterParams.value})
});

watch(filterParams.value, () => {
  console.log('category id ', filterParams.category_id)
  transactionsStore.loadTransactions({...filterParams.value})
});
</script>

<template>
  <div class="flex-container">
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">{{ TransactionsTitle }}</h3>
      </div>
      <div class="mx-2 total-filtro">
        <h5 class="mt-4">Total: {{ transactionsStore.totalTransactions }}</h5>
      </div>
    </div>
    <hr>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <!-- Transaction Type Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectType" class="form-label">Filter by Type:</label>
        <select class="form-select" id="selectType" v-model="filterParams.transaction_type">
          <option value="">Any</option>
          <option value="D">Debit</option>
          <option value="C">Credit</option>
        </select>
      </div>

      <!-- Payment Type Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectPaymentType" class="form-label">Filter by Payment Type:</label>
        <select class="form-select" id="selectPaymentType" v-model="filterParams.payment_type">
          <option value="">Any</option>
          <option value="VCARD">VCARD</option>
          <option value="MB">Multibanco</option>
          <!-- Add other payment types -->
        </select>
      </div>

      <!-- Vcard Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectVcard" class="form-label">Filter by Vcard:</label>
        <input type="text" class="form-control" id="selectVcard" v-model="filterParams.pair_vcard" />
      </div>

      <!-- Category Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectCategory" class="form-label">Filter by Category:</label>
        <select class="form-select" id="selectCategory" v-model="filterParams.category_id">
          <option value="">Any</option>
          <option value="-1">-- No category --</option>
          <option
          v-for="cat in categoriesStore.categories"
          :key="cat.id"
          :value="cat.id"
        >{{cat.name}}</option>
          <!-- Add other categories -->
        </select>
      </div>

      <!-- Min Value Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="filterByMinValue" class="form-label">Minimum Value:</label>
        <input type="number" class="form-control" id="filterByMinValue" v-model="filterParams.min_value" />
      </div>

      <!-- Max Value Filter -->
      <div class="mx-2 mt-2 filter-div">
        <label for="filterByMaxValue" class="form-label">Max Value:</label>
        <input type="number" class="form-control" id="filterByMaxValue" v-model="filterParams.max_value" />
      </div>

      <!-- Start Date Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="filterByStartDate" class="form-label">Filter by Start Date:</label>
        <input type="date" class="form-control" id="filterByStartDate" v-model="filterParams.start_date" />
      </div>

      <!-- End Date Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="filterByEndDate" class="form-label">Filter by End Date:</label>
        <input type="date" class="form-control" id="filterByEndDate" v-model="filterParams.end_date" />
      </div>

      <!-- Add Transaction Button -->
      <div class="mx-2 mt-2">
        <button type="button" class="btn btn-success px-4 btn-addTransaction" @click="addTransaction">
          <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Send Money
        </button>
      </div>
    </div>

    <!-- Transaction Table Component -->
    <div class="table-container">
      <Transaction-table :transactions="transactionsStore.transactions" :categories="categoriesStore.categories" :showId="true" :showOwner="false"
        @edit="editTransaction"></Transaction-table>
    </div>
  </div>
</template>


<style scoped>
.filter-div {
  min-width: 12rem;
}

.total-filtro {
  margin-top: 0.35rem;
}

.btn-addTransaction {
  margin-top: 1.85rem;
}

.flex-container {
  display: flex;
  flex-direction: column;
  height: 93.1vh;
}

.table-container {
  flex-grow: 1;
}
</style>
