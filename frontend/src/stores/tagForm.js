import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useTagFormStore = defineStore('tagFormStore', () => {
  const blankTag = {
    name: '',
    id: null,
  }

  const tag = ref({
    ...blankTag
  })
  const type = ref('add')
  const open = ref(false)

  function updateTag(newTagData) {
    Object.assign(tag.value, newTagData)
  }

  function updateType(newType) {
    type.value = newType
  }

  function updateOpen(newOpen) {
    open.value = newOpen
  }

  function resetTag() {
    tag.value = {
      ...blankTag
    }
  }

  return { tag, updateTag, type, open, updateType, updateOpen, resetTag }
})
