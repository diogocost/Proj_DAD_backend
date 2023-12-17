<script setup>

import { ref, inject } from "vue"; //delete inject
import "ag-grid-community/styles//ag-grid.css";
import "ag-grid-community/styles//ag-theme-quartz.css";
import { AgGridVue } from "ag-grid-vue3";
import avatarNoneUrl from '@/assets/avatar-none.png';

const props = defineProps({
  vcards: Array
});

const serverBaseUrl = inject("serverBaseUrl");

const emit = defineEmits(['delete', 'blockUnblock', 'changeMaxDebit', 'addCredit']);

const columns = ref([
  { headerName: 'Photo', field: 'photo_url', maxWidth:71, cellRenderer: params => {return `<div class="d-flex justify-content-center align-middle"><img src="${params.value ? serverBaseUrl + '/storage/fotos/' + params.value : avatarNoneUrl}" class="rounded-circle img_photo" style="width: 40px; height: 40px;" /></div>`;}},
  { headerName: 'Phone Number', field: 'phone_number', maxWidth:130, sortable: true },
  { headerName: 'Name', field: 'name', sortable: true, flex: 1, minWidth: 200 },
  { headerName: 'Email', field: 'email', sortable: true, flex: 1, minWidth: 200 },
  { headerName: 'Balance', field: 'balance', sortable: true, maxWidth:100 },
  { headerName: 'Max Debit', field: 'max_debit', sortable: true, maxWidth:110 },
  { headerName: 'Blocked', field: 'blocked', sortable: true, maxWidth:83, cellRenderer: params => params.value ? 'Yes' : 'No' },
  {
    headerName: 'Actions',
    field: 'actions',
    flex: 1,
    cellRenderer: function(params) {
      // Create container
      const actionsDiv = document.createElement('div');
      actionsDiv.className = 'd-flex justify-content-between align-middle';

      // Delete button
      const deleteButton = document.createElement('button');
      deleteButton.innerHTML = 'Delete';
      deleteButton.className = 'btn btn-sm btn-danger';
      deleteButton.addEventListener('click', () => emitDelete(params.data));
      actionsDiv.appendChild(deleteButton);

      // Block/Unblock button
      const toggleButton = document.createElement('button');
      toggleButton.className = `btn btn-sm ${params.data.blocked ? 'btn-warning' : 'btn-success'}`;
      toggleButton.innerHTML = params.data.blocked ? 'Unblock' : 'Block';
      toggleButton.addEventListener('click', () => emitBlockUnblock(params.data));
      actionsDiv.appendChild(toggleButton);

      // Change max debit button
      const changeDebitButton = document.createElement('button');
      changeDebitButton.innerHTML = 'Change Debit Limit';
      changeDebitButton.className = 'btn btn-sm btn-primary';
      changeDebitButton.addEventListener('click', () => emitChangeMaxDebit(params.data));
      actionsDiv.appendChild(changeDebitButton);

      // Add credit button
      const addCreditButton = document.createElement('button');
      addCreditButton.innerHTML = 'Add Credit';
      addCreditButton.className = 'btn btn-sm btn-secondary';
      addCreditButton.addEventListener('click', () => emitAddCredit(params.data));
      actionsDiv.appendChild(addCreditButton);

      // Return the complete div with all buttons
      return actionsDiv;
    },
    editable: false,
    filter: false,
    minWidth: 400,
    resizable: true
  }
]);

function emitDelete(vcard) {
  console.log('delete vcard clicked:', vcard);
  emit('delete', vcard);
}

function emitAddCredit(vcard) {
  emit('addCredit', vcard);
}

function emitBlockUnblock(vcard) {
  emit('blockUnblock', vcard);
  //console.log(`${vcard.blocked ? 'Unblock' : 'Block'} vcard clicked:`, vcard);
}

function emitChangeMaxDebit(vcard) {
  // Prompt the user to enter a new max debit value
  const newMaxDebit = prompt('Enter new max debit limit:', vcard.max_debit);

  // Check if the user entered a value and it's different from the current max debit
  if (newMaxDebit !== null && newMaxDebit !== vcard.max_debit && newMaxDebit > 0) {
    // Parse the input to a float to ensure it's a valid number
    const parsedMaxDebit = parseFloat(newMaxDebit);

    // If the parsed number is a valid number, emit the changeMaxDebit event
    if (!isNaN(parsedMaxDebit)) {
      const payload = {
        max_debit: parsedMaxDebit,
      };

      // Emit the changeMaxDebit event with vcard ID and the new max debit value
      emit('changeMaxDebit', { vcard, payload });
      console.log(`change max debit clicked for vcard:`, vcard);
    } else {
      // If the user did not enter a valid number, show an error message
      alert('Please enter a valid number for max debit limit.');
    }
  }
}

</script>

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
