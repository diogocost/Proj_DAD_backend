<script setup>
import axios from 'axios'
import { useRouter } from 'vue-router'
import { ref, computed, onMounted } from 'vue'
import CategoryTable from "./CategoryTable.vue"
import { useUserStore } from '../../stores/user.js'

const router = useRouter()
const userStore = useUserStore()
const filterOptions = [{ label: 'All', value: 'all' }, { label: 'Debit', value: 'D' }, { label: 'Credit', value: 'C' }]; // Filter options
const selectedFilter = ref('all'); // Ref for the selected filter

const filteredCategories = computed(() => {
    if (selectedFilter.value === 'all') {
        return categories.value;
    }
    return categories.value.filter(category => category.type === selectedFilter.value);
});

const loadCategories = async () => {
    const userId = userStore.userId
    try {
        const response = await axios.get('vcards/' + userId + '/categories')
        categories.value = response.data.data
    } catch (error) {
        console.log(error)
    }
}

const addCategory = () => {
    router.push({ name: 'NewCategories' })
}

const editCategory = (category) => {
    router.push({ name: 'Category', params: { id: category.id } })
}


const deletedCategory = (deletedCategory) => {
    let idx = categories.value.findIndex((c) => c.id === deletedCategory.id)
    if (idx >= 0) {
        categories.value.splice(idx, 1);
    }
}

const props = defineProps({
    categoriesTitle: {
        type: String,
        default: 'Categories'
    },
})

const categories = ref([])

const totalCategories = computed(() => {
    return filteredCategories.value.length;
});

onMounted(() => {
    loadCategories()
})
</script>

<template>
    <div class="actions-container">
        <!-- Add Category Button -->
        <div class="add-category-container">
            <button type="button" class="btn btn-success px-4 btn-addtask" @click.prevent="addCategory">
                <i class="bi bi-xs bi-plus-circle"></i>&nbsp; Add Category
            </button>
        </div>

        <!-- Filter Dropdown -->
        <div class="filter-container">
            <select v-model="selectedFilter" class="form-select">
                <option v-for="option in filterOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                </option>
            </select>
        </div>
    </div>
    <div class="d-flex justify-content-between">
        <div class="mx-2">
            <h3 class="mt-4">{{ categoriesTitle }}</h3>
        </div>
        <div class="mx-2 total-filtro">
            <h5 class="mt-4">Total: {{ totalCategories }}</h5>
        </div>
    </div>
    <hr>
    <category-table :categories="filteredCategories" :showId="true" :showType="true" @edit="editCategory"
        @deleted="deletedCategory"></category-table>
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

.add-category-container,
.filter-container {
    margin-bottom: 1rem;
    /* Spacing between the elements */
}

.actions-container {
    display: flex;
    flex-direction: column;
    /* Stack children vertically */
    align-items: start;
    /* Align items to the start of the container */
}</style>
