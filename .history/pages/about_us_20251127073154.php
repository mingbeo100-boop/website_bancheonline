 <style>
   /* Nền gradient động */
   body {
     margin: 0;
     font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
     background: linear-gradient(-45deg, #f7d9ff, #d1f7ff, #ffe5e5, #d8ffd6);
     background-size: 400% 400%;
     animation: gradientBG 15s ease infinite;
     color: #333;
   }

   @keyframes gradientBG {
     0% {
       background-position: 0% 50%;
     }

     50% {
       background-position: 100% 50%;
     }

     100% {
       background-position: 0% 50%;
     }
   }

   /* Phần giới thiệu */
   .about-section {
     text-align: center;
     padding: 60px 20px;
   }

   .about-section h2 {
     font-size: 36px;
     color: #2c2c2c;
     margin-bottom: 20px;
   }

   .about-section p {
     font-size: 18px;
     max-width: 800px;
     margin: auto;
     color: #444;
   }

   /* Danh sách thành viên */
   .founders {
     display: flex;
     flex-wrap: wrap;
     justify-content: center;
     gap: 40px;
     margin-top: 50px;
   }

   .founder {
     width: 200px;
     transition: transform 0.3s ease;
     cursor: pointer;
   }

   .founder img {
     width: 100%;
     height: 320px;
     object-fit: cover;
     border-radius: 20px;
     box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
     transition: transform 0.3s ease;
   }

   .founder img:hover {
     transform: scale(1.05) translateY(-10px);
     box-shadow: 0 20px 30px rgba(0, 0, 0, 0.35);
   }

   .founder h4 {
     margin-top: 15px;
     font-size: 20px;
     color: #2c2c2c;
   }

   .founder p {
     font-size: 16px;
     color: #555;
   }

   /* Nút quay về trang chủ */
   .back-button {
     margin-top: 60px;
   }

   .back-button a {
     display: inline-block;
     padding: 12px 24px;
     font-size: 16px;
     background-color: #2c3e50;
     color: white;
     text-decoration: none;
     border-radius: 30px;
     transition: background-color 0.3s ease;
   }

   .back-button a:hover {
     background-color: #34495e;
   }

   @media (max-width: 768px) {
     .founder {
       max-width: 90%;
     }
   }
 </style>



 <div class="about-section">
   <h2>VỀ NHÓM CHÚNG TÔI</h2>
   <p>
     Chúng tôi là nhóm sinh viên IT trẻ đầy đam mê và sáng tạo đến từ VKU - Đại học Đà Nẵng. Với mong muốn tạo ra một
     sản phẩm phục vụ cộng đồng và lưu giữ những nét văn hóa ẩm thực độc đáo, chúng tôi cùng nhau xây dựng dự án này.
   </p>

   <div class="founders">
    <div class="founder">
        <img src="./assets/images/quan.jpg" alt="Nguyễn Minh Quân" />
        <h4>Nguyễn Minh Quân</h4>
        <p>C.E.O CMO</p>
      </div>
    <div class="founder">
        <img src="./assets/images/quy.jpg" alt="Hoàng Dương Thanh Quý" />
        <h4>Hoàng Dương Thanh Quý</h4>
        <p>C.E.O MANAGER</p>
      </div>
     <div class="founder">
       <img src="assets/images/review_form/long.jpg" alt="Trần Nhật Long" />
       <h4>Trần Nhật Long</h4>
       <p>C.E.O MMO</p>
     </div>
     <div class="founder">
       <img src="assets/images/review_form/minh.jpg" alt="Nguyễn Nhật Minh" />
       <h4>Nguyễn Nhật Minh</h4>
       <p>C.E.O FOUNDER</p>
     </div>
     <div class="founder">
       <img src="assets/images/review_form/chidat.jpg" alt="Hoàng Dương Thanh Quý" />
       <h4>Nguyễn Chí Đạt</h4>
       <p>C.E.O CTO</p>
     </div>
   </div>

   <!-- Nút quay về -->
   <div class="back-button">
     <a href="index.php?page=home"><i class="fas fa-arrow-left"></i> Quay về Trang Chủ</a>
   </div>
 </div>