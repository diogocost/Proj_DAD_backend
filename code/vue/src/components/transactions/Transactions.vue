<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useUserStore } from "../../stores/user.js"
import { ref, computed, onMounted, watch } from 'vue'
import TransactionTable from "./TransactionTable.vue"
import { useTransactionsStore } from "../../stores/transactions.js"
import { useCategoriesStore } from '../../stores/categories'
import { useToast } from "vue-toastification"

const router = useRouter()
const toast = useToast()
const userStore = useUserStore()
const transactionsStore = useTransactionsStore()
const categoriesStore = useCategoriesStore()

const maxValue = ref(null)

const filterParams = ref({
  start_date: '',
  end_date: '',
  transaction_type: '',
  category_id: '',
  pair_vcard: '',
  payment_type: '',
  min_value: 0,
  max_value: maxValue.value,
})

const categories = ref(categoriesStore.categories)

const errors = ref(null)

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

const applyFilters = async () => {
  try {
    errors.value = null
    await transactionsStore.loadTransactions({ ...filterParams.value })
    toast.success('Filters applied successfully!')
  } catch (error) {
    if (error.response?.status == 422) {
      errors.value = error.response.data.errors
      toast.error('Error applying filters!')
    } else {
      toast.error('Error applying filters!')
    }
  }
}

const clearFilters = () => {
  filterParams.value = {
    start_date: '',
    end_date: '',
    transaction_type: '',
    category_id: '',
    pair_vcard: '',
    payment_type: '',
    min_value: 0,
    max_value: maxValue.value,
  }
  applyFilters()
}

watch(
  () => filterParams.value.category_id,
  (category_id) => {
    if (category_id == '-1' || category_id == '') {
      filterParams.value.transaction_type = ''
      return
    }
    const category = categoriesStore.categories.find(c => c.id == category_id)
    if (category.type != filterParams.value.transaction_type) {
      filterParams.value.transaction_type = category.type
    }
  }
)

watch(
  () => filterParams.value.transaction_type,
  (transaction_type) => {
    if (transaction_type == 'C') {
      categories.value = categoriesStore.categoriesCredit
    } else if (transaction_type == 'D') {
      categories.value = categoriesStore.categoriesDebit
    } else {
      categories.value = categoriesStore.categories
    }
  }
)

onMounted( async () => {
  categories.value = categoriesStore.categories
  await transactionsStore.loadTransactions({ ...filterParams.value })
  maxValue.value = Math.max(...transactionsStore.transactions.map(t => t.value))
  filterParams.value.max_value = maxValue.value
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
    <div class="d-flex justify-content-between flex-wrap">
      <!-- Transaction Type Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectType" class="form-label">Filter by Type:</label>
        <select class="form-select" id="selectType" v-model="filterParams.transaction_type" :disabled="filterParams.category_id != '' && filterParams.category_id != '-1'"
        :class="{ 'is-invalid': errors ? errors['transaction_type'] : false }">
          <option value="">Any</option>
          <option value="D">Debit</option>
          <option value="C">Credit</option>
        </select>
        <field-error-message :errors="errors" fieldName="transaction_type"></field-error-message>
      </div>

      <!-- Payment Type Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectPaymentType" class="form-label">Filter by Payment Type:</label>
        <select class="form-select" id="selectPaymentType" v-model="filterParams.payment_type" :class="{ 'is-invalid': errors ? errors['payment_type'] : false }">
          <option value="">Any</option>
          <option value="VISA">Visa</option>
          <option value="VCARD">Vcard</option>
          <option value="MB">Multibanco</option>
          <option value="PAYPAL">Paypal</option>
          <option value="IBAN">IBAN</option>
          <option value="MBWAY">Mbway</option>
          <!-- Add other payment types -->
        </select>
        <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
      </div>

      <!-- Category Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="selectCategory" class="form-label">Filter by Category:</label>
        <select class="form-select" id="selectCategory" v-model="filterParams.category_id" :class="{ 'is-invalid': errors ? errors['category_id'] : false }">
          <option value="">Any</option>
          <option value="-1">-- No category --</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          <!-- Add other categories -->
        </select>
        <field-error-message :errors="errors" fieldName="category_id"></field-error-message>
      </div>

      <!-- Vcard Filter -->
      <div class="mx-2 mt-2 filter-div">
        <label for="selectVcard" class="form-label">Filter by Vcard:</label>
        <input type="text" class="form-control" id="selectVcard" v-model="filterParams.pair_vcard" :class="{ 'is-invalid': errors ? errors['pair_vcard'] : false }"/>
        <field-error-message :errors="errors" fieldName="pair_vcard"></field-error-message>
      </div>
      <div class="mx-2 mt-2">
        <button type="button" class="btn px-4 btn-dark btn-addTransaction" @click="clearFilters">
          <i class="bi bi-xs bi-stars"></i> Clear
        </button>
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-between flex-wrap">

      <!-- Min Value Filter -->
      <div class="mx-2 mt-2 filter-div">
        <label for="filterByMinValue" class="form-label">Minimum Value:</label>
        <input min="0" type="number" class="form-control" id="filterByMinValue" v-model="filterParams.min_value" :class="{ 'is-invalid': errors ? errors['min_value'] : false }"/>
        <field-error-message :errors="errors" fieldName="min_value"></field-error-message>
      </div>

      <!-- Max Value Filter -->
      <div class="mx-2 mt-2 filter-div">
        <label for="filterByMaxValue" class="form-label">Max Value:</label>
        <input :min="filterParams.min_value" type="number" class="form-control" id="filterByMaxValue"
          v-model="filterParams.max_value" :class="{ 'is-invalid': errors ? errors['max_value'] : false }"/>
        <field-error-message :errors="errors" fieldName="max_value"></field-error-message>
      </div>

      <!-- Start Date Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="filterByStartDate" class="form-label">Filter by Start Date:</label>
        <input type="date" class="form-control" id="filterByStartDate" v-model="filterParams.start_date" :class="{ 'is-invalid': errors ? errors['start_date'] : false }"/>
        <field-error-message :errors="errors" fieldName="start_date"></field-error-message>
      </div>

      <!-- End Date Filter -->
      <div class="mx-2 mt-2 flex-grow-1 filter-div">
        <label for="filterByEndDate" class="form-label">Filter by End Date:</label>
        <input type="date" class="form-control" id="filterByEndDate" v-model="filterParams.end_date" :class="{ 'is-invalid': errors ? errors['end_date'] : false }"/>
        <field-error-message :errors="errors" fieldName="end_date"></field-error-message>
      </div>

      <!-- Apply filters -->
      <div class="mx-2 mt-2">
        <button type="button" class="btn px-4 btn-primary btn-addTransaction" @click="applyFilters">
          <i class="bi bi-xs bi-funnel"></i> Filter
        </button>
      </div>

      <!-- Add Transaction Button -->
      <div class="mx-2 mt-2">
        <button type="button" class="btn btn-success px-4 btn-addTransaction" @click="addTransaction">
          <i class="bi bi-xs bi-plus-circle"></i> Send
        </button>
      </div>
    </div>

    <!-- Transaction Table Component -->
    <div class="table-container">
      <Transaction-table :transactions="transactionsStore.transactions" :categories="categories"
        :showId="true" :showOwner="false" @edit="editTransaction"></Transaction-table>
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
