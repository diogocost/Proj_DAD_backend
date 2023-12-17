<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { ref, watch, computed } from "vue"
import "ag-grid-community/styles//ag-grid.css";
import "ag-grid-community/styles//ag-theme-quartz.css";
import { AgGridVue } from "ag-grid-vue3";


const toast = useToast()

const props = defineProps({
  transactions: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: false,
  },
  showPair_transaction: {
    type: Boolean,
    default: false,
  },
  showEditButton: {
    type: Boolean,
    default: true,
  },
})

const columns = ref([
  { headerName: 'Date', field: 'datetime', sortable: true, sort: 'desc', sortingOrder: ['asc', 'desc'] },
  { headerName: 'Type', field: 'type', minWidth: 70, maxWidth: 90, sortable: false },
  {
    headerName: 'Value', field: 'value', sortable: true, minWidth: 80, maxWidth: 100, sortingOrder: ['asc', 'desc'],
    comparator: (valueA, valueB, nodeA, nodeB, isInverted) => {
      // Compare numeric values for sorting
      const numA = parseFloat(valueA);
      const numB = parseFloat(valueB);

      if (numA === numB) {
        return 0;
      }

      return numA < numB ? -1 : 1;
    },
  },
  { headerName: 'Old balance', field: 'old_balance', minWidth: 100, maxWidth: 110, sortable: false },
  { headerName: 'New balance', field: 'new_balance', minWidth: 100, maxWidth: 110, sortable: false },
  { headerName: 'Payment Type', field: 'payment_type', minWidth: 100, maxWidth: 110, sortable: false },
  { headerName: 'Payment Reference', field: 'payment_reference', sortable: false },
  { headerName: 'Category', field: 'category_name', sortable: false, minWidth: 100, maxWidth: 150 },
  { headerName: 'Actions', field: 'actions', sortable: false, flex: 1, cellRenderer: function (params) {
      // Custom rendering for the Actions column
      const button = document.createElement('button');
      button.innerHTML = '<i class="bi bi-xs bi-pencil"></i>';
      button.className = 'btn btn-sm btn-light';
      button.addEventListener('click', () => editClick(params.data)); // Call your editClick function

      const div = document.createElement('div');
      div.appendChild(button);

      return div;
    } }, // Add actions column
]);

function editClick(transaction) {
  emit('edit', transaction);
}

const emit = defineEmits(["completeToggled", "edit"])

const editingTransactions = ref(props.transactions)
const isDataLoaded = ref(false); // Flag to track data loading state
watch(
  () => props.transactions,
  (newTransactions) => {
    editingTransactions.value = newTransactions
  }
)
setTimeout(() => {
  isDataLoaded.value = true;
}, 1000); // Simulating a delay of 2 seconds
</script>

<template>
  <ag-grid-vue style="width: 100%; height: 100%;" :rowHeight="45" class="ag-theme-quartz" :columnDefs="columns"
    :rowData="editingTransactions" :pagination="true" :paginationPageSize="50">
  </ag-grid-vue>
</template>

<style scoped>
.completed {
  text-decoration: line-through;
}

button {
  margin-left: 3px;
  margin-right: 3px;
}

.sort-icons {
  display: inline-block;
  margin-left: 4px;
  font-size: small;
}
</style>
