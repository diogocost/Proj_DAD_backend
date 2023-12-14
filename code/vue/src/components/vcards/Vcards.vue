<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { useVcardsStore } from '@/stores/vcards';
import VcardTable from './VcardTable.vue';
import { useRouter } from 'vue-router'
import { useToast } from "vue-toastification"
const toast=useToast();

const router = useRouter();


const store = useVcardsStore();
const filterParams = ref({
  phone_number: '',
  name: '',
  email: '',
  blocked: '',
  min_debit: null, 
  max_debit: null, 
});

// Fetch initial data
onMounted( async () => {
  await store.fetchVcards({ ...filterParams.value })
  
});
// Reactive data for vcards and total vcards count
const vcards = computed(() => store.vcards);
const totalVcards = computed(() => vcards.value.length);

// Watch for filter changes and fetch new data
watch(filterParams, (newValues) => {
  store.fetchVcards(newValues);
  
}, { deep: true });



const handleDelete = (vcard) => {
  store.deleteVcard(vcard.id); // Make sure 'id' matches the property name of vCard's ID
  console.log("Deleting vcard with ID:", vcard.id);

};

const handleBlockUnblock = (vcard) => {

  const actionType = vcard.blocked ? 'unblock' : 'block';
  store.manageVcard(vcard.id, actionType);


};

const handleChangeMaxDebit = (vcard) => {
  const newMaxDebit = prompt("Enter new max debit limit:", vcard.max_debit);
  if (newMaxDebit !== null && newMaxDebit !== "") {
    store.manageVcard(vcard.id, 'changeMaxDebit', { newMaxDebit });
  }
};


const errors = ref(null);

const applyFilters = async () => {
  try {
    errors.value = null;
    await store.fetchVcards(filterParams.value);
    toast.success('Filters applied successfully!');
  } catch (error) {
    if (error.response?.status == 422) {
      errors.value = error.response.data.errors;
      toast.error('Error applying filters!');
    } else {
      toast.error('Unexpected error occurred!');
    }
  }
};


const clearFilters = () => {
  filterParams.value = {
    phone_number: '',
    name: '',
    email: '',
    blocked: '',
    min_debit: null,
    max_debit: null,
  };
  applyFilters();
};

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
          <select id="filterByBlockedStatus" v-model="filterParams.blocked" class="form-control">
            <option value="">Any</option>
            <option value="true">Blocked</option>
            <option value="false">Unblocked</option>
          </select>
        </div>
        <!-- Blocked/Unblocked Filter -->

        <!-- Min Debit Filter -->
        <div class="filter-input">
          <label for="filterByMinDebit">Min Debit Limit:</label>
          <input type="number" id="filterByMinDebit" v-model.number="filterParams.min_debit" class="form-control"
            placeholder="Min Debit Limit" min="0" />
        </div>

        <!-- Max Debit Filter -->
        <div class="filter-input">
          <label for="filterByMaxDebit">Max Debit Limit:</label>
          <input type="number" id="filterByMaxDebit" v-model.number="filterParams.max_debit" class="form-control"
            placeholder="Max Debit Limit" :min="filterParams.min_debit || 0" />
        </div>

        <div class="filter-input">
          <label for="filterByPhoneNumber">Phone Number:</label>
          <input type="text" id="filterByPhoneNumber" v-model="filterParams.phone_number" class="form-control"
            placeholder="Filter by Phone Number">
        </div>
        <div class="filter-input">
          <label for="filterByName">Name:</label>
          <input type="text" id="filterByName" v-model="filterParams.name" class="form-control"
            placeholder="Filter by Name">
        </div>
        <div class="filter-input">
          <label for="filterByEmail">Email:</label>
          <input type="text" id="filterByEmail" v-model="filterParams.email" class="form-control"
            placeholder="Filter by Email">
        </div>

        <div class="mx-2 mt-2">
          <button type="button" class="btn px-4 btn-dark btn-addTransaction" @click="clearFilters">
            <i class="bi bi-xs bi-stars"></i> Clear
          </button>
        </div>
        <!-- Add other filters here if necessary -->

      </div>
    </div>

    <!-- Vcard Table Component -->
    <VcardTable :vcards="vcards" @delete="handleDelete"  @blockUnblock="handleBlockUnblock"
      @changeMaxDebit="handleChangeMaxDebit">
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
