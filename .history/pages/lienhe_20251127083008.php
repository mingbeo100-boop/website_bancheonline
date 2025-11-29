<style>
    h1 {
        font-family: 'Dancing Script', cursive;
        color: #E6B8A2;
        font-size: 58px;
        text-align: center;
        margin-bottom: 30px;
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

<h1>Liên hệ với chúng tôi!</h1>

<div class="decor-form">
    <form>
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
</div>