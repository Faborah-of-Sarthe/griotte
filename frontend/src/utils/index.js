import { useUserStore } from '../stores/user.js'

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
