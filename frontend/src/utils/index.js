// Check if the user is logged in
export function isLoggedIn() {
  return localStorage.getItem('user')
}

// Save the user in local storage
export function saveUser(user) {
    localStorage.setItem('user', JSON.stringify(user))
}
