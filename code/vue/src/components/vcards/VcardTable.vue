<template>
  <ag-grid-vue style="width: 100%; height: 100%;" class="ag-theme-alpine" :columnDefs="columns" :rowData="vcards"
    :pagination="true" :paginationAutoPageSize="true"></ag-grid-vue>
</template>

<script setup>
import { AgGridVue } from 'ag-grid-vue3';
import 'ag-grid-community/styles/ag-grid.css';
import 'ag-grid-community/styles/ag-theme-alpine.css';


defineProps({
  vcards: Array
});

const columns = [
  // Define columns here
  { headerName: 'Phone Number', field: 'phone_number' },
  // Other column definitions...
];
</script>

<style scoped>
.ag-theme-alpine {
  height: 600px;
  /* Adjust as needed */
  width: 100%;
}

.ag-header-cell,
.ag-cell {
  text-align: left;
  padding: 0.5rem;
}

.ag-header-cell {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
}

.ag-row {
  background-color: white;
}

.ag-row:nth-child(even) {
  background-color: #f2f2f2;
}

.ag-row:hover {
  background-color: #e9ecef;
}

.ag-cell {
  border-right: 1px solid #dee2e6;
}

.ag-cell:last-child {
  border-right: none;
}

.button-cell button {
  margin-right: 0.5rem;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 0.5rem 1rem;
  cursor: pointer;
}

.button-cell button:hover {
  background-color: #0056b3;
}
</style>