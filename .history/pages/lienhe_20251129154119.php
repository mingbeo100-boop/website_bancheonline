<style>
    h1 {
        font-family: 'Dancing Script', cursive;
        color: #E6B8A2;
        font-size: 58px;
        text-align: center;
        margin-bottom: 30px;
        width: fit-content;
        margin: auto;
    }

    .decor-form {
        background: rgba(0, 0, 0, 0.3);
        padding: 30px 40px;
        border-radius: 20px;
        max-width: 450px;
        margin: auto;
        border: 1px solid #333;
        backdrop-filter: blur(3px);
    }

    label {
        color: white;
        font-size: 18px;
        margin-bottom: 8px;
        display: block;
    }

    input {
        width: 100%;
        padding: 10px 12px;
        margin-bottom: 18px;
        border-radius: 10px;
        border: 1px solid #ccc;
        font-size: 16px;
        outline: none;
    }

    input:focus {
        border-color: #E6B8A2;
        box-shadow: 0 0 5px #E6B8A2;
    }

    button {
        width: 100%;
        padding: 12px;
        background-color: #E6B8A2;
        color: white;
        border: none;
        font-size: 18px;
        font-weight: bold;
        border-radius: 10px;
        cursor: pointer;
        transition: 0.3s;
    }

    button:hover {
        background-color: #d9a48f;
    }

    textarea {
        width: 100%;
        padding: 10px 12px;
    }
</style>

<h1 class="text-center my-4 p-3">Liên hệ với chúng tôi!</h1>

<div class="decor-form">
    <form id="contactForm">
        <input type="hidden" name="access_key" value="761cdf31-22e1-4275-8f28-8b4115701e05">

        <label>Họ và tên</label>
        <input type="text" name="name" required>

        <label>Số điện thoại</label>
        <input type="tel" name="number" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Tin nhắn</label>
        <textarea name="message" rows="5"></textarea>

        <button type="submit">GỬI</button>
    </form>
    <div id="formMessage" style="color: yellow; margin-top: 10px;"></div>
</div>


<script>
   document.getElementById("contactForm").addEventListener("submit", async function(e) {
    e.preventDefault(); // Ngăn reload trang

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch("https://api.web3forms.com/submit", {
            method: "POST",
            body: formData
        });

        const result = await response.json();

        if(result.success){
            Swal.fire({
                icon: 'success',
                title: 'Gửi thành công!',
                text: 'Chúng tôi sẽ liên hệ bạn sớm.',
                confirmButtonColor: '#E6B8A2'
            });
            form.reset();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Có lỗi xảy ra: ' + result.message,
                confirmButtonColor: '#E6B8A2'
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Có lỗi xảy ra: ' + error.message,
            confirmButtonColor: '#E6B8A2'
        });
    }
});

</script>