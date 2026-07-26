import axios, { type AxiosRequestConfig, type AxiosResponse } from 'axios'

export interface AjaxInstance {
    get<T>(url: string, config?: AxiosRequestConfig): Promise<T>
    post<T>(
        url: string,
        data?: unknown,
        config?: AxiosRequestConfig
    ): Promise<T>
    put<T>(url: string, data?: unknown, config?: AxiosRequestConfig): Promise<T>
    patch<T>(
        url: string,
        data?: unknown,
        config?: AxiosRequestConfig
    ): Promise<T>
    delete<T>(url: string, config?: AxiosRequestConfig): Promise<T>
}

export const createCommonAxiosInstance = (): AjaxInstance => {
    const ajax = axios.create()

    ajax.interceptors.response.use(
        (response: AxiosResponse) => {
            return response.data
        },
        (error) => {
            return Promise.reject(error)
        }
    )

    return ajax as AjaxInstance
}

const ajax = createCommonAxiosInstance()

export default ajax
