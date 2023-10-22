import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useSectionFormStore = defineStore('sectionFormStore', () => {

  const blankSection = {
    name: '',
    id: null,
    color: '',
    icon: '',
  }

  // State
  const section = ref({
    ...blankSection
  })
  const type = ref('add');
  const open = ref(false);


  // Actions
  function updateSection(newSectionData) {
    Object.assign(section.value, newSectionData)
  }

  function updateType(newType) {
    type.value = newType;
  }

  function updateOpen(newOpen) {
    open.value = newOpen;
  }

  function resetSection() {
    section.value = {
      ...blankSection
    }
  }

  return { section, updateSection, type, open, updateType, updateOpen, resetSection}
})
