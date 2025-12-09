/**
 * Tên file: assets/js/review.js
 * Mục đích: Xử lý việc gửi đánh giá, và CẬP NHẬT nội dung đánh giá mẫu (giữ nguyên avatar gốc).
 * Giả định: SweetAlert2 đã được load.
 */

document.addEventListener('DOMContentLoaded', function() {

    // --- 1. HÀM TẠO HTML NỘI DUNG ---
    
    /**
     * Tạo chuỗi HTML cho phần sao (rating).
     */
    function createStarHTML(rating) {
        // Tạo chuỗi ngôi sao (⭐⭐⭐⭐⭐)
        return '⭐'.repeat(rating); 
    }

    // --- 2. HÀM XỬ LÝ GỬI ĐÁNH GIÁ ---
    
    function handleReviewSubmission(event) {
        event.preventDefault(); 

        // 1. Lấy dữ liệu từ form (Dùng ID đã được gán trong HTML)
        const nameInput = document.querySelector('#userReview input[name="name"]'); 
        const ratingInput = document.getElementById('rating'); 
        const reviewTextInput = document.querySelector('#userReview textarea[name="comment"]'); 
        
        const name = nameInput ? nameInput.value.trim() : '';
        const rating = ratingInput ? parseInt(ratingInput.value || 0) : 0; 
        const reviewText = reviewTextInput ? reviewTextInput.value.trim() : '';

        if (!name || rating < 1 || rating > 5 || !reviewText) {
            Swal.fire('Thiếu thông tin', 'Vui lòng nhập đầy đủ Tên, chọn số sao (1-5) và nội dung đánh giá.', 'warning');
            return;
        }
        
        // 2. Chọn đánh giá MẪU ngẫu nhiên để cập nhật
        const reviewsContainer = document.getElementById('reviewList'); // ID của container
        const existingReviews = reviewsContainer ? reviewsContainer.querySelectorAll('.review-card') : [];
        
        const reviewElements = Array.from(existingReviews);
        
        if (reviewElements.length > 0) {
            // Chọn một chỉ mục ngẫu nhiên để cập nhật
            const randomIndex = Math.floor(Math.random() * reviewElements.length);
            const reviewToUpdate = reviewElements[randomIndex]; 
            
            // 🔥 CẬP NHẬT NỘI DUNG NODE (Giữ nguyên thẻ <img> avatar) 🔥
            
            // Cập nhật Tên
            const nameElement = reviewToUpdate.querySelector('.text_reviews h3');
            if (nameElement) {
                nameElement.textContent = name;
            }
            
            // Cập nhật Số sao
            const ratingElement = reviewToUpdate.querySelector('.text_reviews p');
            if (ratingElement) {
                ratingElement.textContent = createStarHTML(rating);
            }

            // Cập nhật Chú thích (Review Content)
            const contentElement = reviewToUpdate.querySelector('.review-content p');
            if (contentElement) {
                // Thêm dấu ngoặc kép vào nội dung mới (như trong mẫu HTML)
                contentElement.textContent = `"${reviewText}"`; 
            }
            
        } else {
             // Thông báo nếu không tìm thấy đánh giá mẫu
             console.error("Không tìm thấy đánh giá mẫu để cập nhật.");
        }

        // 3. Dọn dẹp form và thông báo
        
        // Reset form input
        if (nameInput) nameInput.value = '';
        if (reviewTextInput) reviewTextInput.value = '';
        if (ratingInput) ratingInput.value = ''; // Reset select về "Mức độ hài lòng"
        
        Swal.fire({
            title: 'Cảm ơn!',
            text: 'Đánh giá của bạn đã được ghi nhận và hiển thị!',
            icon: 'success',
            showConfirmButton: false,
            timer: 2500
        });
        
        // 🔥 THỰC TẾ: Gọi API Backend để lưu trữ đánh giá này vào database
        // saveReviewToDatabase(name, rating, reviewText, avatarPath, oldReviewerName); 
    }

    // --- 3. GÁN SỰ KIỆN ---
    
    // Gán sự kiện cho form có ID là 'userReview' (đã sửa trong HTML)
    const reviewForm = document.getElementById('userReview');
    if (reviewForm) {
        reviewForm.addEventListener('submit', handleReviewSubmission);
    }
});