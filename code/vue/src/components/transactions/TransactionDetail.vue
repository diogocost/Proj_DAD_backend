<script setup>
import { ref, watch, computed } from 'vue'
import { useUserStore } from '../../stores/user';
import { useCategoriesStore } from '../../stores/categories';

const props = defineProps({
  transaction: {
    type: Object,
    required: true
  },
  operationType: {
    type: String,
    default: 'insert'  // insert / update
  },
  errors: {
    type: Object,
    required: false,
  },
})

const emit = defineEmits(['save', 'cancel'])
const categoriesStore = useCategoriesStore()
const editingTransaction = ref(props.transaction)
watch(
  () => props.transaction,
  (newTransaction) => {
    categoriesStore.loadCategories()
    editingTransaction.value = newTransaction
  }
)

const transactionTitle = computed(() => {
  if (!editingTransaction.value) {
    return ''
  }
  return props.operationType == 'insert' ? 'New Transaction' : 'Transaction #' + editingTransaction.value.id
})

const save = () => {
  emit('save', editingTransaction.value)
}

const cancel = () => {
  emit('cancel', editingTransaction.value)
}
</script>

<template>
  <form class="row g-3 needs-validation" novalidate @submit.prevent="save">
    <h3 class="mt-5 mb-3">{{ transactionTitle }}  - {{ editingTransaction.datetime }}</h3>
    <hr>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <!-- Value -->
      <div class="me-3 filter-div flex-grow-1">
        <label for="inputValue" class="form-label">Value</label>
        <input type="number" class="form-control" id="inputValue" placeholder="Transaction Value" :disabled="props.operationType == 'update'"
          v-model="editingTransaction.value">
      </div>
      <!-- Transaction Type -->
      <div class="me-3 filter-div flex-grow-1">
        <label for="selectType" class="form-label">Type:</label>
        <select class="form-select" id="selectType" v-model="editingTransaction.type" :disabled="props.operationType == 'update'">
          <option :disabled="props.operationType == 'insert' && useUserStore.userIsAdmin" value="D">Debit</option>
          <option value="C">Credit</option>
        </select>
      </div>
      <!-- Payment Type -->
      <div class="me-3 filter-div flex-grow-1">
        <label for="selectPaymentType" class="form-label">Payment Type:</label>
        <select class="form-select" id="selectPaymentType" v-model="editingTransaction.payment_type" :disabled="props.operationType == 'update'">
          <option value="VISA">Visa</option>
          <option value="VCARD">Vcard</option>
          <option value="MB">Multibanco</option>
          <option value="PAYPAL">Paypal</option>
          <option value="IBAN">IBAN</option>
          <option value="MBWAY">Mbway</option>
        </select>
      </div>

      <!-- Payment Reference -->
      <div class="me-3 filter-div flex-grow-1" v-if="editingTransaction.payment_type != 'VCARD'">
        <label for="inputPaymentReference" class="form-label">Payment Reference</label>
        <input type="number" class="form-control" id="inputPaymentReference" placeholder="Payment Reference" :disabled="props.operationType == 'update'"
          v-model="editingTransaction.payment_reference">
      </div>

      <!-- Pair Vcard -->
      <div class="me-3 filter-div flex-grow-1" v-if="editingTransaction.payment_type == 'VCARD'">
        <label for="inputPairVcard" class="form-label">Pair Vcard</label>
        <input type="number" class="form-control" id="inputPairVcard" placeholder="Pair Vcard" :disabled="props.operationType == 'update'"
          v-model="editingTransaction.payment_reference">
      </div>
      <!-- Category -->
      <div class="filter-div flex-grow-1">
        <label for="selectCategory" class="form-label">Filter by Category:</label>
        <select class="form-select" id="selectCategory" v-model="editingTransaction.category_id">
          <option :value="null">-- No category --</option>
          <option v-for="cat in categoriesStore.categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>
    </div>
    
    <!-- Description -->
    <div class="mb-3 flex-grow-1 ">
      <label for="inputDescription" class="form-label">Description</label>
      <textarea type="text" class="form-control" :class="{ 'is-invalid': errors ? errors['description'] : false }"
        id="inputDescription" placeholder="Transaction Description" required
        v-model="editingTransaction.description"></textarea>
      <field-error-message :errors="errors" fieldName="description"></field-error-message>
    </div>
    <div class="mb-3 d-flex justify-content-end">
      <button type="button" class="btn btn-primary px-5" @click="save">Save</button>
      <button type="button" class="btn btn-light px-5" @click="cancel">Cancel</button>
    </div>
  </form>
</template>


<style scoped>
.total_hours {
  width: 26rem;
}

.checkCompleted {
  min-height: 2.375rem;
}
</style>
