<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { ref, watch, computed } from "vue"
import { useDefaultCategoriesStore } from "../../stores/defaultCategories"
import { AgGridVue } from "ag-grid-vue3";

const toast = useToast()

const props = defineProps({
    defaultCategories: {
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
const emit = defineEmits(["edit", "deleted"])

const editingDefaultCategories = ref(props.defaultCategories)
const defaultCategoryToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

const defaultCategoriesStore = useDefaultCategoriesStore()

watch(
    () => props.defaultCategories,
    (NewDefaultCategories) => {
        editingDefaultCategories.value = NewDefaultCategories
    }
)


const columns = ref([
    { headerName: '#', field: 'id', sortable: true, sort: 'asc', flex: 1 },
    { headerName: 'Name', field: 'name', sortable: false, sortingOrder: ['asc', 'desc'], flex: 1 },
    {
        headerName: 'Type', field: 'type', sortable: false, flex: 1,
        cellRenderer: function (params) {
            // Custom rendering for the Type column
            if (params.value == 'C') {
                return 'Credit'
            } else if (params.value == 'D') {
                return 'Debit'
            } else {
                return params.value
            }
        }
    },
    {
        headerName: 'Edit', field: 'edit', sortable: false, maxWidth: 100, minWidth: 100, cellRenderer: function (params) {
            // Custom rendering for the Actions column
            const button = document.createElement('button');
            button.innerHTML = '<i class="bi bi-xs bi-pencil"></i>';
            button.className = 'btn btn-sm btn-light mx-2 my-1';
            button.addEventListener('click', () => editClick(params.data));

            const div = document.createElement('div');
            div.className = 'd-flex justify-content-start';
            div.appendChild(button);

            return div;
        }
    }, // Add actions column
    {
        headerName: 'Delete', field: 'delete', sortable: false, maxWidth: 100, minWidth: 100, cellRenderer: function (params) {
            // Custom rendering for the Actions column
            const button = document.createElement('button');
            button.innerHTML = '<i class="bi bi-xs bi-x-square-fill"></i>';
            button.className = 'btn btn-sm btn-light mx-2 my-1';
            button.addEventListener('click', () => deleteClick(params.data));

            const div = document.createElement('div');
            div.className = 'd-flex justify-content-start';
            div.appendChild(button);

            return div;
        }
    }
]);

const editClick = (defaultCategory) => {
    emit("edit", defaultCategory)
}

const deleteClick = (defaultCategory) => {
    defaultCategoryToDelete.value = defaultCategory
    deleteConfirmationDialog.value.show()
}

const deleteCategoryConfirmed = async () => {
    try {
        let deletedCategory = await defaultCategoriesStore.deleteDefaultCategory(defaultCategoryToDelete.value)
        toast.info(`Category ${defaultCategoryToDeleteName.value} was deleted`)
        emit("deleted", deletedCategory)
    } catch (error) {
        console.log(error)
        toast.error(`It was not possible to delete Category ${defaultCategoryToDeleteName.value}!`)
    }
}

const defaultCategoryToDeleteName = computed(() => defaultCategoryToDelete.value
    ? `#${defaultCategoryToDelete.value.id} (${defaultCategoryToDelete.value.name})`
    : "")

</script>

<template>
    <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Delete Category"
        :msg="`Do you really want to delete the Category ${defaultCategoryToDeleteName}?`"
        @confirmed="deleteCategoryConfirmed">
    </confirmation-dialog>

    <ag-grid-vue style="width: 100%; height: 100%;" :rowHeight="45" class="ag-theme-quartz" :columnDefs="columns"
        :rowData="editingDefaultCategories">
    </ag-grid-vue>
</template>

<style scoped>
.completed {
    text-decoration: line-through;
}

button {
    margin-left: 3px;
    margin-right: 3px;
}</style>
