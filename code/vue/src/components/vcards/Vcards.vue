<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useVcardsStore } from '@/stores/vcards';
import VcardTable from './VcardTable.vue';
import { useRouter } from 'vue-router';
import { useToast } from "vue-toastification";


const router = useRouter();
const toast = useToast();

const store = useVcardsStore();
const filterParams = ref({
  phone_number: '',
  name: '',
  email: '',
  blocked: '',
  max_debit: '',
});

// Fetch initial data
onMounted(() => {
  //store.fetchVcards(filterParams.value);
  fetchFunction()
});

// Reactive data for vcards and total vcards count
const vcards = computed(() => store.vcards);
const totalVcards = computed(() => vcards.value.length);

// Watch for filter changes and fetch new data
watch(filterParams, () => {
  //store.fetchVcards(filterParams.value);
  fetchFunction()
}, { deep: true });




const handleDelete = async (vcard) => {
console.log(vcard)
  if(parseFloat(vcard.balance) == '0') {
    await store.deleteVcard(vcard.phone_number); // Make sure 'id' matches the property name of vCard's ID
    fetchFunction()
    toast.success('Vcard #'+vcard.phone_number+ ' deleted successfully');
  } else {
    toast.error('Vcard balance its not 0€ can not be deleted');
  }
};

const handleBlockUnblock = async (vcard) => {
  await store.blockUnblock(vcard);
  fetchFunction()
};

const handleChangeMaxDebit = async (data) => {
  await store.changeMaxDebitVcard(data.vcard, data.payload)
  fetchFunction()
};

const handleAddCredit = async (vcard) => {
  //let test = -2
  //router.push({ name: 'NewTransactionAddCredit', params: { id: test } })
  router.push({ name: 'NewTransactionAddCredit', query: { vcard_id: vcard.phone_number } });
}

const fetchFunction = () => {
  store.fetchVcards(filterParams.value);
}
</script>


<template>
  <div class="flex-container">
    <div class="d-flex justify-content-between">
      <div class="mx-2">
        <h3 class="mt-4">Vcards Management</h3>
      </div>
      <div class="mx-2 total-filtro">
        <h5 class="mt-4">Total: {{ totalVcards }}</h5>
      </div>
    </div>
    <hr>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
      <!-- Filters Section -->
      <div class="filter-container">
        <!-- Add more filters as needed -->
         <!-- Blocked/Unblocked Filter -->
      <div class="filter-input">
        <label for="filterByBlockedStatus">Blocked Status:</label>
        <select id="filterByBlockedStatus" v-model="filterParams.blocked" class="form-select">
          <option selected value="">Any</option>
          <option value="true">Blocked</option>
          <option value="false">Unblocked</option>
        </select>
      </div>
      <!-- Blocked/Unblocked Filter -->
        <div class="filter-input">
          <label for="filterByPhoneNumber">Phone Number:</label>
          <input type="text" id="filterByPhoneNumber" v-model="filterParams.phone_number" class="form-control" placeholder="Filter by Phone Number">
        </div>
        <div class="filter-input">
          <label for="filterByName">Name:</label>
          <input type="text" id="filterByName" v-model="filterParams.name" class="form-control" placeholder="Filter by Name">
        </div>
        <div class="filter-input">
          <label for="filterByEmail">Email:</label>
          <input type="text" id="filterByEmail" v-model="filterParams.email" class="form-control" placeholder="Filter by Email">
        </div>
        <!-- Add other filters here if necessary -->
        
      </div>
    </div>

    <!-- Vcard Table Component -->
    <VcardTable 
      :vcards="vcards" 
      @delete="handleDelete" 
      @blockUnblock="handleBlockUnblock"  
      @changeMaxDebit="handleChangeMaxDebit"
      @addCredit="handleAddCredit">
    </VcardTable>
  </div>
</template>


<style scoped>
.flex-container {
  display: flex;
  flex-direction: column;
  height: 93.1vh;
}

.filter-container {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1rem;
}

.filter-input {
  flex-grow: 1;
  min-width: 200px;
}

.filter-button {
  padding: 0.5rem 1rem;
  background-color: #4CAF50;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.filter-button:hover {
  background-color: #45a049;
}

.total-filtro {
  margin-top: 0.35rem;
}
</style>
