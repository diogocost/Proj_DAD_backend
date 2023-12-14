<script setup>
  import { ref, watch, computed } from 'vue'

  const props = defineProps({
    defaultCategory: {
      type: Object,
      required: true
    },
    operationType: {
      type: String,
      default: 'insert'  // insert / update
    },
    errors: {
      type: Object,
      required: false,
    },
  })

  const emit = defineEmits(['save', 'cancel'])

  const editingDefaultCategory = ref(props.defaultCategory)

  watch(
    () => props.defaultCategory,
    (newCategory) => {
      editingDefaultCategory.value = newCategory
    }
  )

  const defaultCategoryTitle = computed( () => {
    if (!editingDefaultCategory.value) {
        return ''
      }
      return props.operationType == 'insert' ? 'New Default Category' : 'Default Category #' + editingDefaultCategory.value.id
  })

  const save = () => {
    emit('save', editingDefaultCategory.value)
  }

  const cancel = () => {
    emit('cancel', editingDefaultCategory.value)
  }
</script>

<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    @submit.prevent="save"
  >
    <h3 class="mt-5 mb-3">{{ defaultCategoryTitle }}</h3>
    <hr>
    <div class="mb-3">
      <label
        for="inputName"
        class="form-label"
      >Name</label>
      <input
        type="text"
        class="form-control"
        :class="{'is-invalid': errors ? errors['name']: false}"
        id="inputName"
        placeholder="Default category Name"
        required
        v-model="editingDefaultCategory.name"
      >
      <field-error-message :errors="errors" fieldName="name"></field-error-message>
    </div>
    <div class="mb-3">
      <label
        for="inputType"
        class="form-label"
      >Type</label>
      <select
        class="form-select"
        :class="{ 'is-invalid': errors ? errors['type'] : false }"
        id="inputType"
        v-model="editingDefaultCategory.type"
      >
        <option value="C">Credit</option>
        <option value="D" >Debit</option>
      </select>
      <field-error-message :errors="errors" fieldName="type"></field-error-message>
    </div>
    <div class="mb-3 d-flex justify-content-end">
      <button
        type="button"
        class="btn btn-primary px-5"
        @click="save"
      >Save</button>
      <button
        type="button"
        class="btn btn-light px-5"
        @click="cancel"
      >Cancel</button>
    </div>
  </form>
</template>
