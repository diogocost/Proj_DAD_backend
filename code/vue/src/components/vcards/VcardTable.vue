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


import { ref} from "vue"
import "ag-grid-community/styles//ag-grid.css";
import "ag-grid-community/styles//ag-theme-quartz.css";
import { AgGridVue } from "ag-grid-vue3";

const props = defineProps({
  vcards: Array
});

const emit = defineEmits(['delete', 'block', 'unblock', 'changeMaxDebit', 'blockUnblock']);

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
      actionsDiv.className = 'button-cell';

      // Block/Unblock button with toggle icon
      const blockUnblockButton = document.createElement('button');
      blockUnblockButton.innerHTML = params.data.blocked ? '<i class="bi bi-unlock"></i>' : '<i class="bi bi-lock"></i>';
      blockUnblockButton.className = 'btn btn-sm action-button';
      blockUnblockButton.addEventListener('click', () => emitBlockUnblock(params.data));
      actionsDiv.appendChild(blockUnblockButton);

      // Change max debit button
      const changeDebitButton = document.createElement('button');
      changeDebitButton.innerHTML = '<i class="bi bi-credit-card"></i>';
      changeDebitButton.className = 'btn btn-sm action-button';
      changeDebitButton.addEventListener('click', () => emitChangeMaxDebit(params.data));
      actionsDiv.appendChild(changeDebitButton);

      // Delete button
      const deleteButton = document.createElement('button');
      deleteButton.innerHTML = '<i class="bi bi-trash"></i>';
      deleteButton.className = 'btn btn-sm action-button';
      deleteButton.addEventListener('click', () => emitDelete(params.data));
      actionsDiv.appendChild(deleteButton);

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

// Inside your Vue component script

function emitBlockUnblock(vcard) {
  const actionType = vcard.blocked ? 'unblock' : 'block';
  const payload = {
    vcardId: vcard.id,  // Make sure you have the correct vcard ID here
    blocked: actionType === 'block',
  };
  emit('blockUnblock', payload); // This should match the name in your emits option
  console.log(`${actionType} vcard clicked:`, vcard);
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
