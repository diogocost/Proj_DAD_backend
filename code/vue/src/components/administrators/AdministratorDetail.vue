<script setup>
  import { ref, watch, computed } from 'vue'

  const props = defineProps({
    administrator: {
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

  const editingAdministrator = ref(props.administrator)

  console.log(editingAdministrator)

  watch(
    () => props.administrator,
    (newAdministrator) => {
      editingAdministrator.value = newAdministrator
    },
    {immediate : true}
  )

  const administratorsTitle = computed( () => {
    if (!editingAdministrator.value) {
        return ''
      }
      return props.operationType == 'insert' ? 'New Administrator' : 'Administrator #' + editingAdministrator.value.id
  })

  const save = () => {
    emit('save', editingAdministrator.value)
  }

  const cancel = () => {
    emit('cancel', editingAdministrator.value)
  }
</script>

<template>
  <form
    class="row g-3 needs-validation"
    novalidate
    @submit.prevent="save"
  >
    <h3 class="mt-5 mb-3">{{ administratorsTitle }}</h3>
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
        placeholder="Name"
        required
        v-model="editingAdministrator.name"
      >
      <field-error-message :errors="errors" fieldName="name"></field-error-message>
    </div>
    <div class="mb-3">
      <label
        for="inputEmail"
        class="form-label"
      >Email</label>
      <input
        type="text"
        class="form-control"
        :class="{'is-invalid': errors ? errors['email']: false}"
        id="inputEmail"
        placeholder="Email"
        required
        v-model="editingAdministrator.email"
      >
      <field-error-message :errors="errors" fieldName="email"></field-error-message>
    </div>
    <div class="mb-3">
      <label
        for="inputPassword"
        class="form-label"
      >Password</label>
      <input
        type="password"
        class="form-control"
        :class="{'is-invalid': errors ? errors['password']: false}"
        id="inputPassword"
        placeholder="Password"
        required
        v-model="editingAdministrator.password"
      >
      <field-error-message :errors="errors" fieldName="password"></field-error-message>
    </div>
    <div class="mb-3">
          <label for="inputPasswordConfirmation" class="form-label">Password Confirmation</label>
          <input
              type="password"
              class="form-control"
              :class="{ 'is-invalid': errors ? errors['password_confirmation'] : false }"
              id="inputPasswordConfirmation"
              placeholder="Password Confirmation"
              v-model="editingAdministrator.password_confirmation"
          />
          <field-error-message :errors="errors" fieldName="password_confirmation"></field-error-message>
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
