import Cookies from "universal-cookie"

// Check if the user is logged in
export function isLoggedIn() {
  const cookies = new Cookies()
  return cookies.get("XSRF-TOKEN") ? true : false
}

// Save the user in local storage
export function saveUser(user) {
    localStorage.setItem('user', JSON.stringify(user))
}


// Default options for fetch requests
export const defaultOptions = {
  credentials: 'include',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
}