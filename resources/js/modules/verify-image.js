window.verifyImage = async (event) => {
    let isVerified

    let data = new FormData();
    data.append('image', $(event.target).prop('files')[0]);

    await axios.post('/api/image/verify', data).then((res) => {
        isVerified = res.data.isVerified;
    }).catch((error)=>{
        console.error(error)
    })

    return isVerified
}
