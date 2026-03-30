import axios from 'axios'
import router from './router'

axios.defaults.baseURL = 'http://localhost:8000'
axios.defaults.withCredentials = true
axios.defaults.withXSRFToken   = true
axios.defaults.headers.common['Accept']       = 'application/json'
axios.defaults.headers.common['Content-Type'] = 'application/json'

axios.interceptors.response.use(
  response => response,
  error => {
    // removido o redirect automático — o guard cuida disso
    return Promise.reject(error)
  }
)

export default axios