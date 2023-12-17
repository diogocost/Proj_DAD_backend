<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { ref, watch, computed } from "vue"
import "ag-grid-community/styles//ag-grid.css";
import "ag-grid-community/styles//ag-theme-quartz.css";
import { AgGridVue } from "ag-grid-vue3";

const toast = useToast()

const gridOptions = ref({
    pagination: true,
    paginationPageSize: 20, // Set the number of rows per page
    rowSelection: 'single', // Set the row selection type if needed
    // Add other grid options as needed
    domLayout: 'autoHeight',
    suppressHorizontalScroll: false,
});

const onGridReady = (params) => {
  gridOptions.value.api = params.api;
}

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    showId: {
        type: Boolean,
        default: true,
    },
    showType: {
        type: Boolean,
        default: true,
    },
    showEditButton: {
        type: Boolean,
        default: true,
    },
    showDeleteButton: {
        type: Boolean,
        default: true,
    },
})

const columns = ref([
    { headerName: 'ID', field: 'id', flex: 1, sortable: true, filter: 'agNumberColumnFilter' },
    { headerName: 'Name', field: 'name', flex: 1, sortable: true, filter: 'agTextColumnFilter' },
    { headerName: 'Type', field: 'type',cellRenderer: params => params.value === 'D' ? 'Debit' : 'Credit', flex: 1,sortable: true, filter: 'agTextColumnFilter' },
    { headerName: 'Edit', field: 'edit',width: 60, sortable: false, cellRenderer: function (params) {
      // Custom rendering for the Actions column
      const button = document.createElement('button');
      button.innerHTML = '<i class="bi bi-xs bi-pencil"></i>';
      button.className = 'btn btn-sm btn-light';
      button.addEventListener('click', () => editClick(params.data)); // Call your editClick function

      const div = document.createElement('div');
      div.appendChild(button);

      return div;
    } },
    { headerName: 'Delete', field: 'delete',width: 75, sortable: false, cellRenderer: function (params) {
      // Custom rendering for the Actions column
      const button = document.createElement('button');
      button.innerHTML = '<i class="bi bi-xs bi-x-square-fill"></i>';
      button.className = 'btn btn-sm btn-light';
      button.addEventListener('click', () => deleteClick(params.data)); // Call your editClick function

      const div = document.createElement('div');
      div.appendChild(button);

      return div;
    } },
]);

const emit = defineEmits(["edit", "deleted"])

const editingCategories = ref(props.categories)
const categoryToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

watch(
    () => props.categories,
    (newCategories) => {
        editingCategories.value = newCategories
    }
)

// Alternative to previous watch
// watchEffect(() => {
//   editingTasks.value = props.tasks
// })

/* const toogleClick = async (task) => {
    try {
        const response = await axios.patch('tasks/' + task.id + '/completed', { completed: !task.completed })
        task.completed = response.data.data.completed
        emit("completeToggled", task)
    } catch (error) {
        console.log(error)
    }
} */

const editClick = (category) => {
    emit("edit", category)
}

const deleteClick = (category) => {
    categoryToDelete.value = category
    deleteConfirmationDialog.value.show()
}

const deleteCategoryConfirmed = async () => {
    try {
        const response = await axios.delete('categories/' + categoryToDelete.value.id)
        let deletedCategory = response.data.data
        toast.info(`Category ${categoryToDeleteName.value} was deleted`)
        emit("deleted", deletedCategory)
        gridOptions.value.api.updateGridOptions({ rowData: editingCategories.value });
    } catch (error) {
        console.log(error)
        toast.error(`It was not possible to delete Category ${categoryToDeleteName.value}!`)
    }
}

const categoryToDeleteName = computed(() => categoryToDelete.value
    ? `#${categoryToDelete.value.id} (${categoryToDelete.value.name})`
    : "")

</script>

<template>
    <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Delete Category"
        :msg="`Do you really want to delete the Category ${categoryToDeleteName}?`" @confirmed="deleteCategoryConfirmed">
    </confirmation-dialog>
    <ag-grid-vue style="width: 100%;" class="ag-theme-quartz" :columnDefs="columns"
        :rowData="editingCategories" :gridOptions="gridOptions" @grid-ready="onGridReady">
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
</style>
