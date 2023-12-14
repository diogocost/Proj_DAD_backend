import axios from 'axios'
import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'

export const useVcardsStore = defineStore('vcards', () => {
  const toast = useToast()
  const vcards = ref([])

  const totalVcards = computed(() => vcards.value.length)

  function clearVcards() {
    vcards.value = []
  }

  async function fetchVcards(filters) {
    try {
      const filterParams = filters

      Object.keys(filterParams).forEach((key) => {
        if (filterParams[key] === '') {
          delete filterParams[key]
        }
      })
      const response = await axios.get('vcards', { filterParams })
      console.log('API response:', response.data)
      console.log('filterParams:', filterParams)

      vcards.value = response.data.data
      return vcards.value
    } catch (error) {
      console.error(error)
      toast.error('Failed to fetch vCards')
    }
  }

  async function manageVcard(vcardId, actionType, actionData = {}) {
    try {
      // Determine the payload based on actionType
      let payload = {}
      if (actionType === 'block' || actionType === 'unblock') {
        payload.blocked = actionType === 'block'
      } else if (actionType === 'changeMaxDebit') {
        payload.max_debit = actionData.newMaxDebit // newMaxDebit needs to be passed as actionData
      }

      const response = await axios.patch(`vcards/${vcardId}`, payload)

      const vcard = vcards.value.find((v) => v.id === vcardId)
      if (vcard) {
        if (data.blocked !== undefined) vcard.blocked = data.blocked
        if (data.max_debit !== undefined) vcard.max_debit = data.max_debit
      }

      toast.success('Vcard updated successfully')
    } catch (error) {
      console.error(error)
      toast.error('Failed to update vCard')
    }
  }

  
  async function deleteVcard(vcardId) {
    try {
      await axios.delete('vcards/'+ vcardId)
      vcards.value = vcards.value.filter((v) => v.id !== vcardId)
      toast.success('Vcard deleted successfully')
    } catch (error) {
      console.error(error)
      toast.error('Failed to delete vCard')
    }
  }

  return {
    vcards,
    totalVcards,
    fetchVcards,
    manageVcard,
    deleteVcard
  }
})
