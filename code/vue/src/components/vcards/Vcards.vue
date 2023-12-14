<template>
  <div>
    <h1>Vcards Management</h1>

    <!-- Filters Section -->
    <div>
      <input v-model="filterParams.phone_number" placeholder="Filter by Phone Number">
      <input v-model="filterParams.name" placeholder="Filter by Name">
      <input v-model="filterParams.email" placeholder="Filter by Email">
      <button @click="fetchVcards">Search</button>
    </div>

    <vcard-table :vcards="filteredVcards" @manage="manageVcard" @delete="deleteVcard"></vcard-table>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useVcardsStore } from '@/stores/vcards';
import VcardTable from './VcardTable.vue';

const store = useVcardsStore();
const filterParams = ref({
  phone_number: '',
  name: '',
  email: ''
});

const fetchVcards = () => store.fetchVcards();

const filteredVcards = computed(() => {
  return store.vcards.filter(vcard =>
    (filterParams.value.phone_number === '' || vcard.phone_number.includes(filterParams.value.phone_number)) &&
    (filterParams.value.name === '' || vcard.name.includes(filterParams.value.name)) &&
    (filterParams.value.email === '' || vcard.email.includes(filterParams.value.email))
  );
});

watch(filterParams, fetchVcards);

const manageVcard = (vcardId, data) => store.manageVcard(vcardId, data);
const deleteVcard = vcardId => store.deleteVcard(vcardId);

fetchVcards();
</script>


<style scoped>
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
  /* Green */
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

.filter-button:hover {
  background-color: #45a049;
}
</style>

