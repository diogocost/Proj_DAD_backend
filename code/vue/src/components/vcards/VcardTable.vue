<template>
<ag-grid-vue 
  style="width: 100%; height: 100%;" 
  class="ag-theme-quartz" 
  :columnDefs="columns" 
  :rowData="vcards"
  :pagination="true" 
  :paginationAutoPageSize="true">
</ag-grid-vue>
</template>

<script setup>


import { ref, defineProps, defineEmits} from "vue"
import "ag-grid-community/styles//ag-grid.css";
import "ag-grid-community/styles//ag-theme-quartz.css";
import { AgGridVue } from "ag-grid-vue3";

const props = defineProps({
  vcards: Array
});

const emit = defineEmits(['delete', 'block', 'unblock', 'changeMaxDebit']);

const columns = ref([
  { headerName: 'Phone Number', field: 'phone_number', sortable: true },
  { headerName: 'Name', field: 'name', sortable: true },
  { headerName: 'Email', field: 'email', sortable: true },
  { headerName: 'Balance', field: 'balance', sortable: true },
  { headerName: 'Max Debit', field: 'max_debit', sortable: true },
  { headerName: 'Blocked', field: 'blocked', sortable: true, cellRenderer: params => params.value ? 'Yes' : 'No' },
  {
    headerName: 'Actions',
    field: 'actions',
    cellRenderer: function(params) {
      // Create container
      const actionsDiv = document.createElement('div');

      // Delete button
      const deleteButton = document.createElement('button');
      deleteButton.innerHTML = '<i class="fa fa-trash"></i>';
      deleteButton.className = 'btn btn-sm btn-danger';
      deleteButton.addEventListener('click', () => emitDelete(params.data));
      actionsDiv.appendChild(deleteButton);

      // Block/Unblock button
      const toggleButton = document.createElement('button');
      toggleButton.className = `btn btn-sm ${params.data.blocked ? 'btn-warning' : 'btn-success'}`;
      toggleButton.innerHTML = params.data.blocked ? '<i class="fa fa-unlock"></i>' : '<i class="fa fa-lock"></i>';
      toggleButton.addEventListener('click', () => emitBlockUnblock(params.data.blocked ? 'unblock' : 'block', params.data));
      actionsDiv.appendChild(toggleButton);

      // Change max debit button
       // Change max debit button
       const changeDebitButton = document.createElement('button');
      changeDebitButton.innerHTML = '<i class="fa fa-credit-card"></i>';
      changeDebitButton.className = 'btn btn-sm btn-primary';
      changeDebitButton.addEventListener('click', () => emitChangeMaxDebit('changeMaxDebit', params.data));
      actionsDiv.appendChild(changeDebitButton);

      // Return the complete div with all buttons
      return actionsDiv;
    },
    editable: false,
    filter: false
  }
]);

function emitDelete(vcard) {
  console.log('delete vcard clicked:', vcard);
  emit('delete', vcard);
}

function emitBlockUnblock(action, vcard) {
  // Construct the payload for blocking/unblocking the vcard
  const payload = {
    blocked: action === 'block',
  };

  // Emit the block/unblock event with vcard ID and the payload
  emit('blockUnblock', { vcardId: vcard.id, payload });
  console.log(`${action} vcard clicked:`, vcard);
}

function emitChangeMaxDebit(action, vcard) {
  // Prompt the user to enter a new max debit value
  const newMaxDebit = prompt('Enter new max debit limit:', vcard.max_debit);

  // Check if the user entered a value and it's different from the current max debit
  if (newMaxDebit !== null && newMaxDebit !== vcard.max_debit) {
    // Parse the input to a float to ensure it's a valid number
    const parsedMaxDebit = parseFloat(newMaxDebit);

    // If the parsed number is a valid number, emit the changeMaxDebit event
    if (!isNaN(parsedMaxDebit)) {
      const payload = {
        max_debit: parsedMaxDebit,
      };

      // Emit the changeMaxDebit event with vcard ID and the new max debit value
      emit('changeMaxDebit', { vcardId: vcard.id, payload });
      console.log(`change max debit clicked for vcard:`, vcard);
    } else {
      // If the user did not enter a valid number, show an error message
      alert('Please enter a valid number for max debit limit.');
    }
  }
}

</script>

<style scoped>
.ag-theme-quartz {
  height: 600px; /* Adjust as needed */
  width: 100%;
}

/* Header styles */
.ag-header-cell {
  background-color: #f8f9fa;
  border-bottom: 2px solid #dee2e6;
  text-align: left;
  padding: 0.5rem;
}

/* Cell styles */
.ag-cell {
  text-align: left;
  padding: 0.5rem;
  border-right: 1px solid #dee2e6;
}

.ag-cell:last-child {
  border-right: none;
}

/* Row styles */
.ag-row {
  background-color: white;
}

.ag-row:nth-child(even) {
  background-color: #f2f2f2;
}

.ag-row:hover {
  background-color: #e9ecef;
}

/* Button styles */
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

/* Completed task style if applicable */
.completed {
  text-decoration: line-through;
}

/* Sorting icons style */
.sort-icons {
  display: inline-block;
  margin-left: 4px;
  font-size: small;
}
</style>
