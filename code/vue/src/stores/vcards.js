import axios from 'axios';
import { ref, computed, inject, onMounted } from 'vue';
import { defineStore } from 'pinia';
import { useToast } from "vue-toastification";
import { useUserStore } from './user';

export const useVcardsStore = defineStore('vcards', () => {
    const toast = useToast();
    const vcards = ref([]);
    const userStore = useUserStore();
    const totalVcards = computed(() => vcards.value.length);
    const socket = inject('socket');

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

            return vcards.value;
        } catch (error) {
            toast.error("Failed to update vCard");
        }
    }

    async function loadVcard(vcardId) {
        try {
            const response = await axios.get(`vcards/${vcardId}`);
            // Assuming response contains vcard data
            return response.data;
        } catch (error) {
            // Handle error
            throw error;
        }
    }

    async function loadVcard(vcardId) {
        try {
            const response = await axios.get(`vcards/${vcardId}`);
            // Assuming response contains vcard data
            return response.data;
        } catch (error) {
            // Handle error
            throw error;
        }
    }

    async function blockUnblock(vcard) {
        try {
            let action = {}
            // Determine the payload based on actionType
            action.blocked = vcard.blocked ? false : true

            const response = await axios.patch(`vcards/${vcard.phone_number}`, action)
            console.log('API response:', response.data)
            if(action.blocked){
                socket.emit('blockedUser', vcard)
            }
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

    async function deleteVcard(vcardId, data) {
        try {
            if(!userStore.userIsAdmin){
                const config = {
                    headers: {
                        "Content-Type": "application/json",
                    },
                    data: data // Pass additional data here
                };
                socket.emit('deletedUser', vcardId)
                await axios.delete(`vcards/${vcardId}`, config);
                userStore.clearUser();
            } else{
                await axios.delete(`vcards/${vcardId}`);
                socket.emit('deletedUser', vcardId)
                vcards.value = vcards.value.filter(v => v.id !== vcardId);
            }
        } catch (error) {
            console.error(error);
            toast.error('Failed to delete vcard');
            throw error;
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
        loadVcard,
        fetchVcards,
        deleteVcard,
        blockUnblock,
        changeMaxDebitVcard,
        fetchTotalBalance
    }
})