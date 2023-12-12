import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useStoreFormStore = defineStore('storeFormStore', () => {

  const blankStore = {
    name: '',
    id: null,
  }

  // State
  const store = ref({
    ...blankStore
  })
  const type = ref('add');
  const open = ref(false);


  // Actions
  function updateStore(newStoreData) {
    Object.assign(store.value, newStoreData)
  }

  function updateType(newType) {
    type.value = newType;
  }

  function updateOpen(newOpen) {
    open.value = newOpen;
  }

  function resetStore() {
    store.value = {
      ...blankStore
    }
  }

  return { store, updateStore, type, open, updateType, updateOpen, resetStore}
})
