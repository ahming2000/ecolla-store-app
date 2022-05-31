const api = axios.create({
    headers: { 'Content-Type': 'application/json' },
})

api.interceptors.request.use(async (config) => {
    config.baseURL = '/api'
    return config
})

api.interceptors.response.use(
    (response) => {
        return response
    },
    (error) => {
        console.error(error)
        return Promise.reject(error)
    }
)

export default api
