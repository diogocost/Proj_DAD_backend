import axios from 'axios'
import { ref, computed, onMounted } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'vue-toastification'

export const useVcardsStore = defineStore('vcards', () => {
    const toast = useToast()
    const vcards = ref([])
    console.log(vcards.value);

    const totalVcards = computed(() => vcards.value.length)

    function clearVcards() {
        vcards.value = []
    }

    async function fetchVcards(filters) {
        try {
            const filterParams = { ...filters };
            for (const key in filterParams) {
                if (filterParams[key] === '') {
                    delete filterParams[key];
                } else if (key === 'blocked') {
                    // Convert 'true'/'false' strings to 1/0 for the backend
                    if (key === 'blocked' && filterParams[key] !== '') {
                        // Convert 'true'/'false' strings to 1/0 for the backend
                        filterParams[key] = filterParams[key] === 'true' ? 1 : filterParams[key] === 'false' ? 0 : filterParams[key];
                    }
                }
            }

            // Ensure correct structure for Axios request
            const response = await axios.get('vcards', { params: filterParams });
            vcards.value = response.data.data;
            console.log('API response:', response.data);
            console.log('filterParams:', filterParams);

            return vcards.value;
        } catch (error) {
            console.error(error);
            clearVcards;
            toast.error('Failed to fetch vCards');
        }
    }



    // async function manageVcard(vcardId, actionType, actionData = {}) {
    //     try {
    //         // Determine the payload based on actionType
    //         let payload = {}
    //         if (actionType === 'block' || actionType === 'unblock') {
    //             payload.blocked = actionType === 'block'
    //         } else if (actionType === 'changeMaxDebit') {
    //             payload.max_debit = actionData.newMaxDebit // newMaxDebit needs to be passed as actionData
    //         }

    //         const response = await axios.patch(`vcards/${vcardId}`, payload)

    //         const vcard = vcards.value.find((v) => v.id === vcardId)
    //         if (vcard) {
    //             if (data.blocked !== undefined) vcard.blocked = data.blocked
    //             if (data.max_debit !== undefined) vcard.max_debit = data.max_debit
    //         }

    //         toast.success('Vcard updated successfully')
    //     } catch (error) {
    //         console.error(error)
    //         toast.error('Failed to update vCard')
    //     }
    // }

    async function blockUnblock(vcard) {
        try {
            let action = {}
            // Determine the payload based on actionType
            action.blocked = vcard.blocked ? false : true

            const response = await axios.patch(`vcards/${vcard.phone_number}`, action)
            console.log('API response:', response.data)

            toast.success('Vcard updated successfully')
        } catch (error) {
            //console.log("HERE catch", vcard)
            console.error(error)
            toast.error(`Failed to ${vcard.blocked ? 'Unblock' : 'Block'} ${vcard.phone_number}`)
        }
    }

    async function changeMaxDebitVcard(vcard, payload) {
        try {
            //console.log("HERE changes try", vcard)
            //console.log("HERE payload",payload)
            let action = {}
            action.max_debit = payload.max_debit // newMaxDebit needs to be passed as actionData

            const response = await axios.patch(`vcards/${vcard.phone_number}`, action)
            console.log('API response:', response.data)

            toast.success('Vcard updated successfully')
        } catch (error) {
            //console.log("HERE catch", vcard)
            console.error(error)
            toast.error(`Failed to Update debit limit from ${vcard.phone_number}`)
        }
    }


    async function deleteVcard(vcard) {
        try {
            await axios.delete('vcards/' + vcard.phone_number)
            //vcards.value = vcards.value.filter((v) => v.id !== vcard.phone_number)
            toast.success('Vcard deleted successfully')
        } catch (error) {
            console.error(error)
            toast.error('Failed to delete vCard')
        }
    }

    async function fetchTotalBalance() {
        try {
            const response = await axios.get('/totalbalance');
            return response.data.total_balance;
        } catch (error) {
            console.error('Error fetching total balance:', error);
        }
    }

    return {
        vcards,
        totalVcards,
        fetchVcards,
        //manageVcard,
        deleteVcard,
        blockUnblock,
        changeMaxDebitVcard,
        fetchTotalBalance
    }
})