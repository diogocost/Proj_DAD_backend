<script setup>
import { ref, watch, computed, onMounted } from 'vue'
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
const userStore = useUserStore()
const categories = ref(null)
const editingTransaction = ref(props.transaction)

const setDefaultValues = () => {
  if (props.operationType === 'insert') {
    if (!userStore.userIsAdmin) {
      categories.value = categoriesStore.categoriesDebit;
    }
  } else {
    if (editingTransaction.value.type == 'D') {
      categories.value = categoriesStore.categoriesDebit;
    } else {
      categories.value = categoriesStore.categoriesCredit;
    }
  }
}


watch(
  () => props.transaction,
  (newTransaction) => {
    editingTransaction.value = newTransaction
  }
)


watch(
  () => editingTransaction.value.type,
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
onMounted(() => {
  setDefaultValues()
})
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
    <h3 class="mt-5 mb-3">{{ transactionTitle }} - {{ editingTransaction.datetime }}</h3>
    <hr>
    <div class="mb-3 d-flex justify-content-between flex-wrap" v-if="userStore.userIsAdmin">
      <!-- Vcard -->
      <div class="me-3 filter-div">
        <label for="inputVcard" class="form-label">Vcard</label>
        <input type="number" class="form-control" id="inputVcard" placeholder="Vcard Number"
          v-model="editingTransaction.vcard">
        <field-error-message :errors="errors" fieldName="vcard"></field-error-message>
      </div>
    </div>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <!-- Value -->
      <div class="me-3 filter-div flex-grow-1">
        <label for="inputValue" class="form-label">Value</label>
        <input type="number" class="form-control" id="inputValue" placeholder="Transaction Value"
          :disabled="props.operationType == 'update'" v-model="editingTransaction.value" :class="{ 'is-invalid': errors ? errors['value'] : false }">
        <field-error-message :errors="errors" fieldName="value"></field-error-message>
      </div>
      <!-- Transaction Type -->
      <div class="me-3 filter-div flex-grow-1">
        <label for="selectType" class="form-label">Type:</label>
        <select class="form-select" id="selectType" v-model="editingTransaction.type" disabled>
          <option value="D">Debit</option>
          <option value="C">Credit</option>
        </select>
        <field-error-message :errors="errors" fieldName="type"></field-error-message>
      </div>
      <!-- Payment Type -->
      <div class="me-3 filter-div flex-grow-1">
        <label for="selectPaymentType" class="form-label">Payment Type:</label>
        <select class="form-select" id="selectPaymentType" v-model="editingTransaction.payment_type"
          :disabled="props.operationType == 'update' || !userStore.userIsAdmin">
          <option value="VISA">Visa</option>
          <option value="VCARD" v-if="!userStore.userIsAdmin">Vcard</option>
          <option value="MB">Multibanco</option>
          <option value="PAYPAL">Paypal</option>
          <option value="IBAN">IBAN</option>
          <option value="MBWAY">Mbway</option>
        </select>
        <field-error-message :errors="errors" fieldName="payment_type"></field-error-message>
      </div>

      <!-- Payment Reference -->
      <div class="me-3 filter-div flex-grow-1" v-if="editingTransaction.payment_type != 'VCARD'">
        <label for="inputPaymentReference" class="form-label">Payment Reference</label>
        <input type="text" class="form-control" id="inputPaymentReference" placeholder="Payment Reference"
          :disabled="props.operationType == 'update'" v-model="editingTransaction.payment_reference" :class="{ 'is-invalid': errors ? errors['payment_reference'] : false }">
        <field-error-message :errors="errors" fieldName="payment_reference"></field-error-message>
      </div>

      <!-- Pair Vcard -->
      <div class="me-3 filter-div flex-grow-1" v-if="editingTransaction.payment_type == 'VCARD'" >
        <label for="inputPairVcard" class="form-label">Pair Vcard</label>
        <input type="text" class="form-control" id="inputPairVcard" placeholder="Pair Vcard"
          :disabled="props.operationType == 'update'" v-model="editingTransaction.pair_vcard" :class="{ 'is-invalid': errors ? errors['pair_vcard'] : false }">
        <field-error-message :errors="errors" fieldName="pair_vcard"></field-error-message>
      </div>
      <!-- Category -->
      <div class="filter-div flex-grow-1">
        <label for="selectCategory" class="form-label">Filter by Category:</label>
        <select class="form-select" id="selectCategory" v-model="editingTransaction.category_id"
          :disabled="userStore.userIsAdmin">
          <option :value="null">-- No category --</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
        <field-error-message :errors="errors" fieldName="category_id"></field-error-message>
      </div>
    </div>

    <!-- Description -->
    <div class="mb-3 flex-grow-1 ">
      <label for="inputDescription" class="form-label">Description</label>
      <textarea type="text" class="form-control" :class="{ 'is-invalid': errors ? errors['description'] : false }"
        id="inputDescription" placeholder="Transaction Description" required v-model="editingTransaction.description"
        :disabled="userStore.userIsAdmin"></textarea>
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
