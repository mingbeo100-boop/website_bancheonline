document.addEventListener('DOMContentLoaded', function() {
    // 1. Lấy tất cả các input có required mà bạn muốn kiểm soát lỗi
    const requiredInputs = document.querySelectorAll('input[required]');

    requiredInputs.forEach(input => {
        // 2. Chặn thông báo lỗi mặc định của trình duyệt trên từng input
        input.addEventListener('invalid', function(event) {
            // Ngăn thông báo lỗi mặc định hiển thị
            event.preventDefault(); 
            
            // Tìm các phần tử liên quan
            const wrapper = input.closest('.input-wrapper');
            if (!wrapper) return; // Đảm bảo input nằm trong .input-wrapper
            
            const errorTooltip = wrapper.querySelector('.error-message-container');

            // 3. Kích hoạt hiển thị lỗi tùy chỉnh và kiểu input lỗi
            if (errorTooltip) {
                errorTooltip.classList.add('active-error');
            }
            input.classList.add('error-style');
        });

        // 4. Reset lỗi khi người dùng bắt đầu nhập liệu
        input.addEventListener('input', function() {
            // Tìm các phần tử liên quan
            const wrapper = input.closest('.input-wrapper');
            if (!wrapper) return;

            const errorTooltip = wrapper.querySelector('.error-message-container');

            // Kiểm tra xem input đã có giá trị chưa
            if (input.value.trim() !== "") {
                // Tắt hiển thị lỗi khi có giá trị
                if (errorTooltip) {
                    errorTooltip.classList.remove('active-error');
                }
                input.classList.remove('error-style');
                // Quan trọng: Phải gọi setCustomValidity("") để trình duyệt biết input đã hợp lệ
                input.setCustomValidity(""); 
            } else {
                // Đảm bảo thông báo lỗi không hiện lại nếu họ xóa hết
                input.setCustomValidity("Cần điền dữ liệu");
            }
        });
        
        // 5. Đảm bảo lỗi được reset khi form được submit thành công (tùy chọn)
        input.addEventListener('change', function() {
            if (input.validity.valid) {
                input.classList.remove('error-style');
                const wrapper = input.closest('.input-wrapper');
                const errorTooltip = wrapper ? wrapper.querySelector('.error-message-container') : null;
                if (errorTooltip) {
                    errorTooltip.classList.remove('active-error');
                }
            }
        });

    });
});