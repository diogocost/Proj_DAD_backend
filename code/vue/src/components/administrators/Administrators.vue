<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'
import AdministratorTable from "./AdministratorTable.vue"

const router = useRouter()

const loadAdministrators = async () => {
    try {
        const response = await axios.get('admins/')
        administrators.value = response.data.data
    } catch (error) {
        console.log(error)
    }
}

const addAdministrator = () => {
    router.push({ name: 'NewAdministrators' })
}

const editAdministrator = (administrator) => {
    router.push({ name: 'Administrator', params: { id: administrator.id } })
}


const deletedAdministrator = (deletedAdministrator) => {
    let idx = administrators.value.findIndex((c) => c.id === deletedAdministrator.id)
    if (idx >= 0) {
        administrators.value.splice(idx, 1)
    }
}

const props = defineProps({
    administratorsTitle: {
        type: String,
        default: 'Administrators'
    },
})

const administrators = ref([])

const totalAdministrators = computed(() => {
    return administrators.value.length;
});

onMounted(() => {
    loadAdministrators()
})
</script>

<template>
    <div class="mb-3 d-flex justify-content-between flex-wrap">
        <div class="mx-2 mt-2">
            <button type="button" class="btn btn-success px-4 btn-addtask" @click.prevent="addAdministrator"><i
                    class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Administrator</button>
        </div>
    </div>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4">{{ administratorsTitle }}</h3>
        </div>
        <div class="mx-2 total-filtro">
            <h5 class="mt-4">Total: {{ totalAdministrators }}</h5>
        </div>
    </div>
    <hr>
    <administrator-table :administrators="administrators" :showId="true" :showEmail="true" @edit="editAdministrator"
        @deleted="deletedAdministrator"></administrator-table>
</template>


<style scoped>
.filter-div {
    min-width: 12rem;
}

.total-filtro {
    margin-top: 0.35rem;
}

.btn-addtask {
    margin-top: 1.85rem;
}
</style>
