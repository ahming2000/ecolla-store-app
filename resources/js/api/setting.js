import api from "../api";

export const createOrigin = async (name, name_en) => {
    let { data } = await api.post('/setting/origin', {
        name: name,
        name_en: name_en,
    })

    return data.origin
}

export const updateOrigin = async (id, name, name_en) => {
    let { data } = await api.patch('/setting/origin/' + id, {
        name: name,
        name_en: name_en,
    })

    return data.origin
}

export const deleteOrigin = async (id) => {
    return await api.delete('/setting/origin/' + id)
}

export const createCategory = async (name, name_en) => {
    let { data } = await api.post('/setting/category', {
        name: name,
        name_en: name_en,
    })

    return data.category
}

export const updateCategory = async (id, name, name_en) => {
    let { data } = await api.patch('/setting/category/' + id, {
        name: name,
        name_en: name_en,
    })

    return data.category
}

export const deleteCategory = async (id) => {
    return await api.delete('/setting/category/' + id)
}
