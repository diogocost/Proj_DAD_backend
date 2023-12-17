<script setup>
import axios from 'axios'
import { useToast } from "vue-toastification"
import { ref, watch, computed } from "vue"

const toast = useToast()

const props = defineProps({
  administrators: {
    type: Array,
    default: () => [],
  },
  showId: {
    type: Boolean,
    default: true,
  },
  showEmail: {
    type: Boolean,
    default: true,
  },
  showDeleteButton: {
    type: Boolean,
    default: true,
  },
})
const emit = defineEmits(["edit", "deleted"])

const editingAdministrators = ref(props.administrators)
const administratorToDelete = ref(null)
const deleteConfirmationDialog = ref(null)

watch(
  () => props.administrators,
  (newAdministrators) => {
    editingAdministrators.value = newAdministrators
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

const deleteClick = (administrator) => {
    administratorToDelete.value = administrator
    deleteConfirmationDialog.value.show()
}

const deleteAdministratorConfirmed = async () => {
    try {
        const response = await axios.delete('admins/' + administratorToDelete.value.id)
        let deletedadministrator = response.data.data
        toast.info(`Administrator ${administratorToDeleteName.value} was deleted`)
        emit("deleted", deletedadministrator)
    } catch (error) {
        console.log(error)
        toast.error(`It was not possible to delete Administrator ${administratorToDeleteName.value}!`)
    }
}

const administratorToDeleteName = computed(() => administratorToDelete.value
    ? `#${administratorToDelete.value.id} (${administratorToDelete.value.name})`
    : "")

</script>

<template>
    <confirmation-dialog ref="deleteConfirmationDialog" confirmationBtn="Delete administrator"
        :msg="`Do you really want to delete the administrator ${administratorToDeleteName}?`" @confirmed="deleteAdministratorConfirmed">
    </confirmation-dialog>

    <table class="table">
        <thead>
            <tr>
                <th v-if="showId">#</th>
                <th>Name</th>
                <th v-if="showEmail">Email</th>
                <th v-if="showDeleteButton"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="administrator in editingAdministrators" :key="administrator.id">
                <td v-if="showId">{{ administrator.id }}</td>
                <td>
                    <span>{{ administrator.name }}</span>
                </td>
                <td v-if="showEmail">{{ administrator.username }}</td>
                <td class="text-end" v-if="showDeleteButton">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-xs btn-light" @click.prevent="deleteClick(administrator)" v-if="showDeleteButton">
                            <i class="bi bi-xs bi-x-square-fill"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</template>

<style scoped>

button {
    margin-left: 3px;
    margin-right: 3px;
}
</style>
