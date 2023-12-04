<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { ref, watch, computed } from "vue"

const toast = useToast()

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

    <table class="table">
        <thead>
            <tr>
                <th v-if="showId">#</th>
                <th>Name</th>
                <th v-if="showType">Type</th>
                <th v-if="showEditButton || showDeleteButton"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="category in editingCategories" :key="category.id">
                <td v-if="showId">{{ category.id }}</td>
                <td>
                    <span>{{ category.name }}</span>
                </td>
                <td v-if="showType">{{ category.type === 'D' ? 'Debito' : category.type === 'C' ? 'Credito' : '' }}</td>
                <td class="text-end" v-if="showEditButton || showDeleteButton">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-xs btn-light" @click.prevent="editClick(category)" v-if="showEditButton">
                            <i class="bi bi-xs bi-pencil"></i>
                        </button>

                        <button class="btn btn-xs btn-light" @click.prevent="deleteClick(category)" v-if="showDeleteButton">
                            <i class="bi bi-xs bi-x-square-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
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
