import { useUserStore } from '../stores/user.js'
import { customRef } from 'vue'

// Save the user to local storage and the store
export const saveUser = function(user) {

  const userStore = useUserStore()

  // Save the user to the store
  userStore.setUser(user)

}

// Delete the user from local storage and the store
export const logout = async function() {
  const userStore = useUserStore()
  userStore.logout()
}


export function useDebouncedRef(value, delay = 200) {
  let timeout
  return customRef((track, trigger) => {
    return {
      get() {
        track()
        return value
      },
      set(newValue) {
        clearTimeout(timeout)
        timeout = setTimeout(() => {
          value = newValue
          trigger()
        }, delay)
      }
    }
  })
}