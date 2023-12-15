<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted, watch } from 'vue'
import DefaultCategoryTable from "./DefaultCategoryTable.vue"
import { useUserStore } from '../../stores/user.js'
import { useDefaultCategoriesStore } from '../../stores/defaultCategories'
import { useToast } from "vue-toastification"

const router = useRouter()
const userStore = useUserStore()
const toast = useToast()
const defaultCategoriesStore = useDefaultCategoriesStore()

const filterParams = ref({
    transaction_type: '',
})

const addDefaultCategory = () => {
    router.push({ name: 'NewDefaultCategory' })
}

const editDefaultCategory = (defaultCategory) => {
    router.push({ name: 'DefaultCategory', params: { id: defaultCategory.id } })
}

const deletedDefaultCategory = async () => {
    defaultCategoriesStore.loadDefaultCategories()
}

const props = defineProps({
    defaultCategoriesTitle: {
        type: String,
        default: 'DefaultCategories'
    },
})

watch(
    () => filterParams.value.transaction_type,
    (transaction_type) => {
        defaultCategoriesStore.filterParams.type = transaction_type
    }
)
onMounted(() => {
    defaultCategoriesStore.loadDefaultCategories()
})
</script>

<template>
    <div class="flex-container">
    <div class="d-flex justify-content-between flex-wrap">
            <button type="button" class="btn btn-success px-4 btn-addtask" @click.prevent="addDefaultCategory"><i
                    class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Default Category</button>
    </div>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4">{{ defaultCategoriesTitle }}</h3>
        </div>
        <div class="mx-2 total-filtro">
            <h5 class="mt-4">Total: {{ defaultCategoriesStore.totalDefaultCategories }}</h5>
        </div>
    </div>
    <hr>

    <div class="d-flex justify-content-between flex-wrap mb-4">
        <!-- Transaction Type Filter -->
        <div class="mx-2 mt-2 filter-div">
            <label for="selectType" class="form-label">Filter by Type:</label>
            <select class="form-select" id="selectType" v-model="filterParams.transaction_type">
                <option value="">Any</option>
                <option value="D">Debit</option>
                <option value="C">Credit</option>
            </select>
        </div>
    </div>
    <default-category-table :defaultCategories="defaultCategoriesStore.filterDefaultCategories" :showId="true" :showType="true"
        @edit="editDefaultCategory" @deleted="deletedDefaultCategory"></default-category-table>
    </div>
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
.flex-container {
  display: flex;
  flex-direction: column;
  height: 93.1vh;
}
</style>
